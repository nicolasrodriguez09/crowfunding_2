<?php

namespace App\Http\Controllers;

use App\Models\ActualizacionProyecto;
use App\Models\Aportacion;
use App\Models\Proveedor;
use App\Models\ProveedorHistorial;
use App\Models\Proyecto;
use App\Models\ProyectoCategoria;
use App\Models\ProyectoModeloFinanciamiento;
use App\Models\Recompensa;
use App\Models\SolicitudDesembolso;
use App\Models\Pago;
use App\Models\VerificacionSolicitud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreatorController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();
        $user = auth()->user();

        $proyectos = Proyecto::where('creador_id', $userId)->get();

        $recaudadoAportaciones = Aportacion::whereHas('proyecto', fn($q) => $q->where('creador_id', $userId))
            ->sum('monto');
        $recaudadoDeclarado = $proyectos->sum('monto_recaudado');
        $recaudado = max($recaudadoAportaciones, $recaudadoDeclarado);

        $metaTotal = $proyectos->sum('meta_financiacion');
        $avance = $metaTotal > 0 ? round(($recaudado / $metaTotal) * 100) . '%' : '0%';

        $colaboradores = Aportacion::whereHas('proyecto', fn($q) => $q->where('creador_id', $userId))
            ->distinct('colaborador_id')
            ->count('colaborador_id');

        $solicitudes = SolicitudDesembolso::with('proyecto')
            ->whereHas('proyecto', fn($q) => $q->where('creador_id', $userId))
            ->get();

        $fondosLiberados = $solicitudes->whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado'])->sum('monto_solicitado');
        $fondosRetenidos = max($recaudado - $fondosLiberados, 0);
        $fondosGastados = Pago::whereHas('solicitud.proyecto', fn($q) => $q->where('creador_id', $userId))
            ->where('estado_auditoria', 'aprobado')
            ->sum('monto');
        $fondosDisponibles = max($fondosLiberados - $fondosGastados, 0);
        $pendientesPorJustificar = max($fondosLiberados - $fondosGastados, 0);
        $transparencia = $fondosLiberados > 0 ? round(($fondosGastados / $fondosLiberados) * 100) : 0;

        $lastAportacion = Aportacion::whereHas('proyecto', fn($q) => $q->where('creador_id', $userId))
            ->latest()
            ->first();
        $lastSolicitud = $solicitudes->sortByDesc('created_at')->first();
        $lastPago = Pago::whereHas('solicitud.proyecto', fn($q) => $q->where('creador_id', $userId))
            ->latest('fecha_pago')
            ->first();

        $movimientos = [];
        if ($lastAportacion) {
            $movimientos[] = [
                'titulo' => 'Último aporte',
                'detalle' => '$' . number_format($lastAportacion->monto, 2),
                'meta' => optional($lastAportacion->created_at)->format('d/m/Y H:i'),
            ];
        }
        if ($lastSolicitud) {
            $movimientos[] = [
                'titulo' => 'Última solicitud',
                'detalle' => '$' . number_format($lastSolicitud->monto_solicitado, 2),
                'meta' => ucfirst($lastSolicitud->estado ?? 'pendiente'),
            ];
        }
        if ($lastPago) {
            $movimientos[] = [
                'titulo' => 'Último gasto aprobado',
                'detalle' => '$' . number_format($lastPago->monto, 2),
                'meta' => ucfirst($lastPago->estado ?? 'pendiente'),
            ];
        }

        $pendientesComprobantes = Pago::whereHas('solicitud.proyecto', fn($q) => $q->where('creador_id', $userId))
            ->where('estado_auditoria', 'pendiente')
            ->count();
        $pendientesVerificacion = VerificacionSolicitud::where('user_id', $userId)
            ->where('estado', 'pendiente')
            ->count();

        $accionesPendientes = [
            ['label' => 'Subir comprobantes pendientes', 'count' => $pendientesComprobantes],
            ['label' => 'Responder comentarios del auditor', 'count' => $pendientesVerificacion],
            ['label' => 'Publicar actualización de proyecto', 'count' => $proyectos->count() ? 1 : 0],
            ['label' => 'Configurar recompensas nuevas', 'count' => 0],
        ];

        $perfilSteps = [
            'Datos personales' => !empty($user->info_personal),
            'Documentos' => !empty($user->foto_perfil),
            'Redes sociales' => !empty($user->redes_sociales),
            'Verificación' => (bool) $user->estado_verificacion,
        ];
        $perfilCompletado = collect($perfilSteps)->every(fn($value) => $value);
        $perfilCompletadoCount = collect($perfilSteps)->filter()->count();

        $heroCta = $proyectos->isEmpty()
            ? ['label' => 'Crear campaña', 'route' => route('creador.proyectos.create')]
            : ['label' => 'Ver mis proyectos', 'route' => route('creador.proyectos')];

        $metrics = [
            'proyectos'          => $proyectos->count(),
            'montoRecaudado'     => $recaudado,
            'colaboradores'      => $colaboradores,
            'avance'             => $avance,
            'metaTotal'          => $metaTotal,
            'fondosRetenidos'    => $fondosRetenidos,
            'fondosLiberados'    => $fondosLiberados,
            'fondosGastados'     => $fondosGastados,
            'fondosDisponibles'  => $fondosDisponibles,
            'pendientesPorJustificar' => $pendientesPorJustificar,
            'transparencia'      => $transparencia,
            'gastos'             => $fondosGastados,
        ];

        return view('creator.dashboard', compact(
            'metrics',
            'heroCta',
            'movimientos',
            'accionesPendientes',
            'perfilSteps',
            'perfilCompletado',
            'perfilCompletadoCount'
        ));
    }

    public function proyectos(Request $request): View
    {
        $search = $request->query('q');
        $estado = $request->query('estado');

        $proyectos = Proyecto::where('creador_id', auth()->id())
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('titulo', 'like', "%{$search}%")
                       ->orWhere('descripcion_proyecto', 'like', "%{$search}%")
                       ->orWhere('categoria', 'like', "%{$search}%");
                });
            })
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->withCount('aportaciones')
            ->withCount([
                'hitos as hitos_cumplidos_count' => fn($q) => $q->where('es_hito', true),
            ])
            ->latest()
            ->get();

        return view('creator.modules.proyectos', compact('proyectos', 'search', 'estado'));
    }

    public function proyectosCrear(): View
    {
        $this->ensureCreatorVerified();

        $categorias = ProyectoCategoria::orderBy('nombre')->get();
        $modelos = ProyectoModeloFinanciamiento::orderBy('nombre')->get();

        return view('creator.modules.proyectos-create', compact('categorias', 'modelos'));
    }

    public function proyectosEditar(Proyecto $proyecto): View
    {
        $this->authorizeProyecto($proyecto, auth()->id());
        $categorias = ProyectoCategoria::orderBy('nombre')->get();
        $modelos = ProyectoModeloFinanciamiento::orderBy('nombre')->get();

        return view('creator.modules.proyectos-edit', compact('proyecto', 'categorias', 'modelos'));
    }

    public function recompensas(Request $request): View
    {
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;

        $niveles = $this->getRecompensasPorProyecto($proyectos, $selectedProjectId);
        $preview = $niveles->first();

        return view('creator.modules.recompensas', compact('niveles', 'preview', 'proyectos', 'selectedProjectId'));
    }

    public function recompensasCrear(): View
    {
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();

        return view('creator.modules.recompensas-create', compact('proyectos'));
    }

    public function recompensasEditar(Recompensa $recompensa): View
    {
        $this->authorizeRecompensa($recompensa, auth()->id());
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();

        return view('creator.modules.recompensas-edit', compact('recompensa', 'proyectos'));
    }

    public function recompensasGestionar(Request $request): View
    {
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;
        $estadoFiltro = $request->query('estado');

        $niveles = $this->getRecompensasPorProyecto($proyectos, $selectedProjectId, $estadoFiltro);

        return view('creator.modules.recompensas-gestion', compact('niveles', 'proyectos', 'selectedProjectId', 'estadoFiltro'));
    }

    public function recompensasPreview(Request $request): View
    {
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;

        $niveles = $this->getRecompensasPorProyecto($proyectos, $selectedProjectId);
        $preview = $niveles->first();

        return view('creator.modules.recompensas-preview', compact('preview', 'niveles', 'proyectos', 'selectedProjectId'));
    }

    public function storeRecompensa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'monto_minimo_aportacion' => ['required', 'numeric', 'min:0'],
            'disponibilidad' => ['nullable', 'integer', 'min:0'],
        ]);

        $proyecto = Proyecto::find($validated['proyecto_id']);
        abort_unless($proyecto && $proyecto->creador_id === $request->user()->id, 403);

        Recompensa::create([
            'proyecto_id' => $validated['proyecto_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'monto_minimo_aportacion' => $validated['monto_minimo_aportacion'],
            'disponibilidad' => $validated['disponibilidad'] ?? null,
        ]);

        return redirect()->route('creador.recompensas', ['proyecto' => $validated['proyecto_id']])
            ->with('status', 'Recompensa publicada.');
    }

    public function updateRecompensa(Request $request, Recompensa $recompensa): RedirectResponse
    {
        $this->authorizeRecompensa($recompensa, $request->user()->id);

        $validated = $request->validate([
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'monto_minimo_aportacion' => ['required', 'numeric', 'min:0'],
            'disponibilidad' => ['nullable', 'integer', 'min:0'],
        ]);

        $proyecto = Proyecto::find($validated['proyecto_id']);
        abort_unless($proyecto && $proyecto->creador_id === $request->user()->id, 403);

        $recompensa->update([
            'proyecto_id' => $validated['proyecto_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'monto_minimo_aportacion' => $validated['monto_minimo_aportacion'],
            'disponibilidad' => $validated['disponibilidad'] ?? null,
        ]);

        return redirect()->route('creador.recompensas', ['proyecto' => $validated['proyecto_id']])
            ->with('status', 'Recompensa actualizada.');
    }

    public function toggleRecompensaEstado(Request $request, Recompensa $recompensa): RedirectResponse
    {
        $this->authorizeRecompensa($recompensa, $request->user()->id);
        $nuevo = $this->descripcionEsPausada($recompensa->descripcion) ? 'activo' : 'pausado';
        $recompensa->descripcion = $this->aplicarEstadoEnDescripcion($recompensa->descripcion, $nuevo);
        $recompensa->save();

        return redirect()->back()->with('status', "Recompensa {$nuevo}.");
    }

    public function eliminarRecompensa(Request $request, Recompensa $recompensa): RedirectResponse
    {
        $this->authorizeRecompensa($recompensa, $request->user()->id);
        $recompensa->delete();

        return redirect()->back()->with('status', 'Recompensa eliminada.');
    }

    public function avances(Request $request): View
    {
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;
        $selectedProject = $proyectos->firstWhere('id', $selectedProjectId);

        $actualizaciones = collect();
        $calificaciones = collect();
        if ($selectedProjectId) {
            $actualizaciones = ActualizacionProyecto::where('proyecto_id', $selectedProjectId)
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->get();

            $calificaciones = \App\Models\Calificacion::with('colaborador')
                ->where('proyecto_id', $selectedProjectId)
                ->latest('fecha_calificacion')
                ->get();
        }

        $projectContext = null;
        if ($selectedProject) {
            $meta = $selectedProject->meta_financiacion ?? 0;
            $recaudado = $selectedProject->monto_recaudado ?? 0;
            $progress = $meta > 0 ? round(($recaudado / $meta) * 100) : 0;
            $projectContext = [
                'estado' => $selectedProject->estado ?? 'borrador',
                'recaudado' => $recaudado,
                'meta' => $meta,
                'progreso' => $progress,
                'hito' => $selectedProject->fecha_limite?->format('d/m/Y') ?? 'Próximo hito por definir',
            ];
        }

        return view('creator.modules.avances', compact('proyectos', 'selectedProjectId', 'actualizaciones', 'selectedProject', 'projectContext', 'calificaciones'));
    }

    public function fondos(Request $request): View
    {
        $userId = auth()->id();
        $proyectos = Proyecto::where('creador_id', $userId)->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;

        $solicitudes = collect();
        $finanzas = [
            'recaudado' => 0,
            'retenido' => 0,
            'liberado' => 0,
            'gastado' => 0,
            'pendiente' => 0,
            'disponible' => 0,
        ];

        if ($selectedProjectId) {
            $this->authorizeProyectoId($selectedProjectId, $userId);
            $solicitudes = SolicitudDesembolso::where('proyecto_id', $selectedProjectId)
                ->orderByDesc('created_at')
                ->get();

            $finanzas = $this->calcularFinanzasProyecto($selectedProjectId);
        }

        $selectedProject = $proyectos->firstWhere('id', $selectedProjectId);
        $projectSummary = null;
        if ($selectedProject) {
            $meta = $selectedProject->meta_financiacion ?? 0;
            $recaudado = max(
                Aportacion::where('proyecto_id', $selectedProject->id)->sum('monto'),
                $selectedProject->monto_recaudado ?? 0
            );
            $progress = $meta > 0 ? min(100, round(($recaudado / $meta) * 100)) : 0;
            $projectSummary = [
                'estado' => $selectedProject->estado ?? 'borrador',
                'meta' => $meta,
                'recaudado' => $recaudado,
                'progress' => $progress,
                'hito' => $selectedProject->fecha_limite?->format('d/m/Y') ?? ($selectedProject->modelo_financiamento ? 'Modelo ' . $selectedProject->modelo_financiamento : 'Sin hito fijo'),
            ];
        }

        $mensajePendientes = $solicitudes->where('estado', 'pendiente')->count();
        $sinFondos = $finanzas['disponible'] <= 0;

        return view('creator.modules.fondos', compact(
            'proyectos',
            'selectedProjectId',
            'selectedProject',
            'solicitudes',
            'finanzas',
            'projectSummary',
            'mensajePendientes',
            'sinFondos'
        ));
    }

    public function fondosHistorial(Request $request): View
    {
        $userId = auth()->id();
        $proyectos = Proyecto::where('creador_id', $userId)->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;

        $solicitudes = collect();
        if ($selectedProjectId) {
            $this->authorizeProyectoId($selectedProjectId, $userId);
            $solicitudes = SolicitudDesembolso::where('proyecto_id', $selectedProjectId)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('creator.modules.fondos-historial', compact('proyectos', 'selectedProjectId', 'solicitudes'));
    }

    public function proveedores(Request $request): View
    {
        $userId = auth()->id();
        $proyectos = Proyecto::where('creador_id', $userId)->get();
        $search = $request->query('q');
        $proyectoFiltro = $request->query('proyecto');

        $proveedoresQuery = Proveedor::with('proyecto')
            ->withAvg('historiales as calificacion_promedio', 'calificacion')
            ->where('creador_id', $userId)
            ->latest();

        if ($search) {
            $proveedoresQuery->where(function ($q) use ($search) {
                $q->where('nombre_proveedor', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%")
                  ->orWhere('info_contacto', 'like', "%{$search}%");
            });
        }

        if ($proyectoFiltro) {
            $proveedoresQuery->where('proyecto_id', $proyectoFiltro);
        }

        $proveedores = $proveedoresQuery->paginate(10)->withQueryString();
        $totalProveedores = Proveedor::where('creador_id', $userId)->count();

        return view('creator.modules.proveedores', compact('proyectos', 'proveedores', 'search', 'proyectoFiltro', 'totalProveedores'));
    }

    public function crearProveedor(): View
    {
        $userId = auth()->id();
        $proyectos = Proyecto::where('creador_id', $userId)->get();

        return view('creator.modules.proveedores-create', compact('proyectos'));
    }

    public function editarProveedor(Proveedor $proveedor): View
    {
        abort_unless($proveedor->creador_id === auth()->id(), 403);
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();

        return view('creator.modules.proveedores-edit', compact('proveedor', 'proyectos'));
    }

    public function showProveedor(Proveedor $proveedor): View
    {
        abort_unless($proveedor->creador_id === auth()->id(), 403);
        $proveedor->load('proyecto', 'historiales');
        $proveedor->loadAvg('historiales as calificacion_promedio', 'calificacion');
        $proyectos = Proyecto::where('creador_id', auth()->id())->get();
        $proveedores = Proveedor::with('proyecto:id,titulo')
            ->withAvg('historiales as calificacion_promedio', 'calificacion')
            ->where('creador_id', auth()->id())
            ->latest()
            ->get(['id', 'nombre_proveedor', 'proyecto_id']);

        return view('creator.modules.proveedores-show', compact('proveedor', 'proyectos', 'proveedores'));
    }

    public function perfil(): View
    {
        return view('creator.modules.perfil');
    }

    public function reportes(Request $request): View
    {
        $userId = auth()->id();
        $proyectos = Proyecto::where('creador_id', $userId)->get();
        $selectedProjectId = $request->query('proyecto') ?? $proyectos->first()?->id;

        $pagos = collect();
        $solicitudes = collect();
        $proveedores = collect();
        $resumen = [
            'totalPagado' => 0,
            'pagosConAdjuntos' => 0,
            'pagosProveedor' => 0,
        ];

        if ($selectedProjectId) {
            $this->authorizeProyectoId($selectedProjectId, $userId);
            $pagos = Pago::with('proveedor', 'solicitud')
                ->whereHas('solicitud', fn($q) => $q->where('proyecto_id', $selectedProjectId))
                ->orderByDesc('fecha_pago')
                ->orderByDesc('id')
                ->get();

            $solicitudes = SolicitudDesembolso::where('proyecto_id', $selectedProjectId)
                ->whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado'])
                ->orderByDesc('created_at')
                ->get();

            $proveedores = Proveedor::where('creador_id', $userId)
                ->where(function ($q) use ($selectedProjectId) {
                    $q->whereNull('proyecto_id')->orWhere('proyecto_id', $selectedProjectId);
                })
                ->orderBy('nombre_proveedor')
                ->get();

            $resumen['totalPagado'] = $pagos->sum('monto');
            $resumen['pagosConAdjuntos'] = $pagos->filter(fn($p) => !empty($p->adjuntos))->count();
            $resumen['pagosProveedor'] = $pagos->count();
        }

        return view('creator.modules.reportes', compact('proyectos', 'selectedProjectId', 'pagos', 'solicitudes', 'proveedores', 'resumen'));
    }

    public function showPago(Pago $pago): View
    {
        $pago->load(['proveedor', 'solicitud.proyecto']);

        abort_unless(optional($pago->solicitud->proyecto)->creador_id === auth()->id(), 403);

        $proyecto = $pago->solicitud->proyecto;
        $adjuntos = collect($pago->adjuntos ?? [])
            ->filter()
            ->map(function ($path) {
                if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                    return ['path' => $path, 'url' => $path];
                }

                $normalized = ltrim(preg_replace('/^public\\//', '', $path), '/');

                if (Storage::disk('public')->exists($normalized)) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }

                if (file_exists(public_path('storage/' . $normalized))) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }

                if (file_exists(public_path($path))) {
                    return ['path' => $path, 'url' => asset($path)];
                }

                return ['path' => $path, 'url' => Storage::url($path)];
            });

        return view('creator.modules.reportes-show', compact('pago', 'proyecto', 'adjuntos'));
    }

    public function showSolicitud(SolicitudDesembolso $solicitud): View
    {
        $solicitud->load('proyecto');
        abort_unless(optional($solicitud->proyecto)->creador_id === auth()->id(), 403);

        $proyecto = $solicitud->proyecto;
        $adjuntos = collect($solicitud->adjuntos ?? [])
            ->filter()
            ->map(function ($path) {
                if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                    return ['path' => $path, 'url' => $path];
                }

                $normalized = ltrim(preg_replace('/^public\\//', '', $path), '/');

                if (Storage::disk('public')->exists($normalized)) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }

                if (file_exists(public_path('storage/' . $normalized))) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }

                if (file_exists(public_path($path))) {
                    return ['path' => $path, 'url' => asset($path)];
                }

                return ['path' => $path, 'url' => Storage::url($path)];
            });

        return view('creator.modules.fondos-solicitud-show', compact('solicitud', 'proyecto', 'adjuntos'));
    }

    public function storeProyecto(Request $request): RedirectResponse
    {
        $this->ensureCreatorVerified();

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion_proyecto' => ['required', 'string'],
            'meta_financiacion' => ['required', 'numeric', 'min:1'],
            'modelo_financiamiento_id' => ['required', 'exists:proyecto_modelos_financiamiento,id'],
            'categoria_id' => ['required', 'exists:proyecto_categorias,id'],
            'ubicacion_geografica' => ['required', 'string', 'max:120'],
            'fecha_limite' => ['required', 'date'],
            'cronograma' => ['required', 'string', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (!is_array($decoded) || empty($decoded)) {
                    $fail('Agrega al menos un hito al cronograma.');
                }
            }],
            'presupuesto' => ['nullable', 'string'],
            'portada' => ['required', 'image', 'max:8192'],
        ]);

        $cronogramaDecoded = $this->decodeJson($validated['cronograma'] ?? null);
        if (empty($cronogramaDecoded) || !is_array($cronogramaDecoded)) {
            return redirect()->back()->withErrors(['cronograma' => 'Agrega al menos un hito al cronograma.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('portada')) {
            $path = $request->file('portada')->store('proyectos', 'public');
        }

        Proyecto::create([
            'titulo' => $validated['titulo'],
            'descripcion_proyecto' => $validated['descripcion_proyecto'] ?? null,
            'meta_financiacion' => $validated['meta_financiacion'],
            'modelo_financiamiento' => $this->resolveModeloNombre($request->input('modelo_financiamiento_id')),
            'categoria' => $this->resolveCategoriaNombre($request->input('categoria_id')),
            'ubicacion_geografica' => $validated['ubicacion_geografica'] ?? null,
            'fecha_limite' => $validated['fecha_limite'] ?? null,
            'cronograma' => $cronogramaDecoded,
            'presupuesto' => $this->decodeJson($validated['presupuesto'] ?? null),
            'creador_id' => $request->user()->id,
            'estado' => 'borrador',
            'monto_recaudado' => 0,
            'imagen_portada' => $path,
        ]);

        return redirect()->back()->with('status', 'Proyecto creado en borrador.');
    }

    public function updateProyecto(Request $request, Proyecto $proyecto): RedirectResponse
    {
        abort_unless($proyecto->creador_id === $request->user()->id, 403);

        $validated = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'descripcion_proyecto' => ['nullable', 'string'],
            'meta_financiacion' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', 'string', 'max:32'],
            'modelo_financiamiento_id' => ['nullable', 'exists:proyecto_modelos_financiamiento,id'],
            'categoria_id' => ['nullable', 'exists:proyecto_categorias,id'],
            'ubicacion_geografica' => ['nullable', 'string', 'max:120'],
            'fecha_limite' => ['nullable', 'date'],
            'cronograma' => ['nullable', 'string'],
            'presupuesto' => ['nullable', 'string'],
            'portada' => ['nullable', 'image', 'max:8192'],
        ]);

        $payload = $validated;
        $payload['modelo_financiamiento'] = $this->resolveModeloNombre($request->input('modelo_financiamiento_id'));
        $payload['categoria'] = $this->resolveCategoriaNombre($request->input('categoria_id'));
        if (array_key_exists('cronograma', $validated)) {
            $payload['cronograma'] = $this->decodeJson($validated['cronograma']);
        }
        if (array_key_exists('presupuesto', $validated)) {
            $payload['presupuesto'] = $this->decodeJson($validated['presupuesto']);
        }
        if ($request->hasFile('portada')) {
            if ($proyecto->imagen_portada) {
                Storage::disk('public')->delete($proyecto->imagen_portada);
            }
            $payload['imagen_portada'] = $request->file('portada')->store('proyectos', 'public');
        }

        $proyecto->update($payload);

        return redirect()->back()->with('status', 'Proyecto actualizado.');
    }

    public function agregarAvance(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $this->authorizeProyecto($proyecto, $request->user()->id);

        $validated = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255', 'required_without:cronograma_hito'],
            'contenido' => ['nullable', 'string'],
            'es_hito' => ['nullable', 'boolean'],
            'cronograma_hito' => ['nullable', 'string', 'max:255'],
            'adjuntos.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $cronogramaHito = $validated['cronograma_hito'] ?? null;
        $titulo = $validated['titulo'] ?? $cronogramaHito ?? 'Hito del proyecto';
        $contenido = $validated['contenido'] ?? null;
        if ($cronogramaHito) {
            $contenido = trim(($contenido ? $contenido . "\n\n" : '') . 'Hito de cronograma marcado como cumplido: ' . $cronogramaHito);
        }

        $paths = $this->storeAdjuntos($request);

        ActualizacionProyecto::create([
            'proyecto_id' => $proyecto->id,
            'titulo' => $titulo,
            'contenido' => $contenido,
            'fecha_publicacion' => now(),
            'es_hito' => (bool) ($validated['es_hito'] ?? false) || (bool) $cronogramaHito,
            'adjuntos' => $paths,
        ]);

        return redirect()->back()->with('status', 'Avance publicado.');
    }

    public function updateAvance(Request $request, Proyecto $proyecto, ActualizacionProyecto $actualizacion): RedirectResponse
    {
        $this->authorizeProyecto($proyecto, $request->user()->id);
        abort_unless($actualizacion->proyecto_id === $proyecto->id, 403);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['nullable', 'string'],
            'es_hito' => ['nullable', 'boolean'],
            'adjuntos.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $paths = $actualizacion->adjuntos ?? [];
        if ($request->hasFile('adjuntos')) {
            $this->deleteAdjuntos($paths);
            $paths = $this->storeAdjuntos($request);
        }

        $actualizacion->update([
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'] ?? null,
            'es_hito' => (bool) ($validated['es_hito'] ?? false),
            'adjuntos' => $paths,
        ]);

        return redirect()->back()->with('status', 'Avance actualizado.');
    }

    public function deleteAvance(Request $request, Proyecto $proyecto, ActualizacionProyecto $actualizacion): RedirectResponse
    {
        $this->authorizeProyecto($proyecto, $request->user()->id);
        abort_unless($actualizacion->proyecto_id === $proyecto->id, 403);

        $this->deleteAdjuntos($actualizacion->adjuntos ?? []);
        $actualizacion->delete();

        return redirect()->back()->with('status', 'Avance eliminado.');
    }

    public function storeProveedor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_proveedor' => ['required', 'string', 'max:255'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'info_contacto' => ['nullable', 'string'],
            'especialidad' => ['nullable', 'string', 'max:120'],
        ]);

        if (!empty($validated['proyecto_id'])) {
            $proyecto = Proyecto::find($validated['proyecto_id']);
            abort_unless($proyecto && $proyecto->creador_id === $request->user()->id, 403);
        }

        Proveedor::create([
            'creador_id' => $request->user()->id,
            'proyecto_id' => $validated['proyecto_id'] ?? null,
            'nombre_proveedor' => $validated['nombre_proveedor'],
            'info_contacto' => $validated['info_contacto'] ?? null,
            'especialidad' => $validated['especialidad'] ?? null,
        ]);

        return redirect()->back()->with('status', 'Proveedor registrado.');
    }

    public function updateProveedor(Request $request, Proveedor $proveedor): RedirectResponse
    {
        abort_unless($proveedor->creador_id === $request->user()->id, 403);

        $validated = $request->validate([
            'nombre_proveedor' => ['required', 'string', 'max:255'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'info_contacto' => ['nullable', 'string'],
            'especialidad' => ['nullable', 'string', 'max:120'],
        ]);

        if (!empty($validated['proyecto_id'])) {
            $proyecto = Proyecto::find($validated['proyecto_id']);
            abort_unless($proyecto && $proyecto->creador_id === $request->user()->id, 403);
        }

        $proveedor->update([
            'nombre_proveedor' => $validated['nombre_proveedor'],
            'proyecto_id' => $validated['proyecto_id'] ?? null,
            'info_contacto' => $validated['info_contacto'] ?? null,
            'especialidad' => $validated['especialidad'] ?? null,
        ]);

        return redirect()->route('creador.proveedores')->with('status', 'Proveedor actualizado.');
    }

    public function deleteProveedor(Proveedor $proveedor): RedirectResponse
    {
        abort_unless($proveedor->creador_id === auth()->id(), 403);

        $proveedor->delete();

        return redirect()->route('creador.proveedores')->with('status', 'Proveedor eliminado.');
    }

    public function storeProveedorHistorial(Request $request, Proveedor $proveedor): RedirectResponse
    {
        abort_unless($proveedor->creador_id === $request->user()->id, 403);

        $validated = $request->validate([
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_entrega' => ['nullable', 'date'],
            'calificacion' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        ProveedorHistorial::create([
            'proveedor_id' => $proveedor->id,
            'concepto' => $validated['concepto'],
            'monto' => $validated['monto'],
            'fecha_entrega' => $validated['fecha_entrega'] ?? null,
            'calificacion' => $validated['calificacion'] ?? null,
        ]);

        return redirect()->back()->with('status', 'Historial registrado.');
    }

    public function storePago(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $this->authorizeProyecto($proyecto, $request->user()->id);

        $validated = $request->validate([
            'solicitud_id' => ['required', 'exists:solicitudes_desembolso,id'],
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['nullable', 'date'],
            'concepto' => ['nullable', 'string'],
            'calificacion' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'adjuntos.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $solicitud = SolicitudDesembolso::find($validated['solicitud_id']);
        abort_unless($solicitud && $solicitud->proyecto_id === $proyecto->id, 403);

        $permitidos = ['aprobado', 'liberado', 'pagado', 'gastado'];
        if (!in_array($solicitud->estado, $permitidos, true)) {
            return redirect()->back()->withErrors(['solicitud_id' => 'Solo puedes asociar pagos a desembolsos aprobados o liberados.'])->withInput();
        }

        $proveedor = Proveedor::find($validated['proveedor_id']);
        abort_unless($proveedor && $proveedor->creador_id === $request->user()->id, 403);
        if ($proveedor->proyecto_id && $proveedor->proyecto_id !== $proyecto->id) {
            return redirect()->back()->withErrors(['proveedor_id' => 'El proveedor no pertenece a este proyecto.'])->withInput();
        }

        $paths = $this->storePagoAdjuntos($request);

        Pago::create([
            'solicitud_id' => $solicitud->id,
            'proveedor_id' => $proveedor->id,
            'monto' => $validated['monto'],
            'fecha_pago' => $validated['fecha_pago'] ?? now(),
            'concepto' => $validated['concepto'] ?? null,
            'adjuntos' => $paths,
        ]);

        // Guardamos también en el historial del proveedor
        ProveedorHistorial::create([
            'proveedor_id' => $proveedor->id,
            'concepto' => $validated['concepto'] ?? 'Pago proveedor',
            'monto' => $validated['monto'],
            'fecha_entrega' => $validated['fecha_pago'] ?? Carbon::now(),
            'calificacion' => $validated['calificacion'] ?? null,
        ]);

        return redirect()->route('creador.reportes', ['proyecto' => $proyecto->id])
            ->with('status', 'Pago registrado con evidencias.');
    }

    public function updatePerfil(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_completo' => ['nullable', 'string', 'max:255'],
            'profesion' => ['nullable', 'string', 'max:120'],
            'experiencia' => ['nullable', 'string'],
            'biografia' => ['nullable', 'string'],
            'info_personal' => ['nullable', 'string'],
            'redes_sociales' => ['nullable', 'array'],
            'redes_sociales.*' => ['nullable', 'url'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = $request->user();
        $user->nombre_completo = $validated['nombre_completo'] ?? $user->nombre_completo;
        $user->profesion = $validated['profesion'] ?? $user->profesion;
        $user->experiencia = $validated['experiencia'] ?? $user->experiencia;
        $user->biografia = $validated['biografia'] ?? $user->biografia;
        $user->info_personal = $validated['info_personal'] ?? $user->info_personal;
        $user->redes_sociales = $validated['redes_sociales'] ?? $user->redes_sociales;

        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }
            $user->foto_perfil = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        $user->save();

        return redirect()->back()->with('status', 'Perfil actualizado.');
    }

    public function solicitarVerificacion(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'documento_frontal' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'documento_reverso' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'selfie' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'nota' => ['nullable', 'string'],
        ]);

        $existe = VerificacionSolicitud::where('user_id', $user->id)
            ->where('estado', 'pendiente')
            ->exists();
        if ($existe) {
            return redirect()->back()->withErrors(['verificacion' => 'Ya tienes una solicitud de verificacion pendiente.'])->withInput();
        }

        $adjuntos = $this->storeKycAdjuntos($request);

        VerificacionSolicitud::create([
            'user_id' => $user->id,
            'estado' => 'pendiente',
            'nota' => $validated['nota'] ?? null,
            'adjuntos' => $adjuntos,
        ]);

        return redirect()->back()->with('status', 'Solicitud de verificacion enviada a administracion.');
    }

    public function verificacion(Request $request): View
    {
        $pendiente = VerificacionSolicitud::where('user_id', $request->user()->id)
            ->where('estado', 'pendiente')
            ->exists();

        return view('creator.modules.perfil-verificacion', compact('pendiente'));
    }

    public function storeSolicitudDesembolso(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $this->authorizeProyecto($proyecto, $request->user()->id);

        $validated = $request->validate([
            'monto_solicitado' => ['required', 'numeric', 'min:1'],
            'hito' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string'],
            'proveedores' => ['nullable', 'string'],
            'fecha_estimada' => ['nullable', 'date'],
            'adjuntos.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $existe = SolicitudDesembolso::where('proyecto_id', $proyecto->id)
            ->where('hito', $validated['hito'])
            ->where('estado', 'pendiente')
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors(['hito' => 'Ya existe una solicitud pendiente para este hito.'])->withInput();
        }

        $finanzas = $this->calcularFinanzasProyecto($proyecto->id);
        if ($validated['monto_solicitado'] > $finanzas['disponible']) {
            return redirect()->back()->withErrors(['monto_solicitado' => 'El monto solicitado excede los fondos disponibles.'])->withInput();
        }

        $proveedores = [];
        if (!empty($validated['proveedores'])) {
            $proveedores = collect(explode(',', $validated['proveedores']))
                ->map(fn($v) => trim($v))
                ->filter()
                ->values()
                ->all();
        }

        $paths = $this->storeDesembolsoAdjuntos($request);

        SolicitudDesembolso::create([
            'proyecto_id' => $proyecto->id,
            'monto_solicitado' => $validated['monto_solicitado'],
            'hito' => $validated['hito'],
            'descripcion' => $validated['descripcion'] ?? null,
            'proveedores' => $proveedores,
            'fecha_estimada' => $validated['fecha_estimada'] ?? null,
            'estado' => 'pendiente',
            'adjuntos' => $paths,
        ]);

        return redirect()->route('creador.fondos', ['proyecto' => $proyecto->id])
            ->with('status', 'Solicitud enviada.');
    }

    private function authorizeProyecto(Proyecto $proyecto, int $userId): void
    {
        abort_unless($proyecto->creador_id === $userId, 403);
    }

    private function authorizeProyectoId(int $proyectoId, int $userId): void
    {
        $proyecto = Proyecto::find($proyectoId);
        abort_unless($proyecto && $proyecto->creador_id === $userId, 403);
    }

    private function ensureCreatorVerified(): void
    {
        if (!auth()->user()->estado_verificacion) {
            abort(403, 'Tu cuenta debe estar verificada para crear nuevos proyectos.');
        }
    }

    private function storeAdjuntos(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $paths[] = $file->store('actualizaciones', 'public');
            }
        }

        return $paths;
    }

    private function storeDesembolsoAdjuntos(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $paths[] = $file->store('desembolsos', 'public');
            }
        }

        return $paths;
    }

    private function storePagoAdjuntos(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $paths[] = $file->store('pagos', 'public');
            }
        }

        return $paths;
    }

    private function storeKycAdjuntos(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('documento_frontal')) {
            $paths['documento_frontal'] = $request->file('documento_frontal')->store('kyc', 'public');
        }
        if ($request->hasFile('documento_reverso')) {
            $paths['documento_reverso'] = $request->file('documento_reverso')->store('kyc', 'public');
        }
        if ($request->hasFile('selfie')) {
            $paths['selfie'] = $request->file('selfie')->store('kyc', 'public');
        }

        return $paths;
    }

    private function deleteAdjuntos(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function calcularFinanzasProyecto(int $proyectoId): array
    {
        $recaudadoAportaciones = Aportacion::where('proyecto_id', $proyectoId)->sum('monto');
        $recaudadoProyecto = Proyecto::where('id', $proyectoId)->value('monto_recaudado') ?? 0;
        $recaudado = max($recaudadoAportaciones, $recaudadoProyecto);
        $solicitudes = SolicitudDesembolso::where('proyecto_id', $proyectoId)->get();

        $liberado = $solicitudes->whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado'])->sum('monto_solicitado');
        $gastado = $solicitudes->where('estado', 'gastado')->sum('monto_solicitado');
        $pendiente = $solicitudes->where('estado', 'pendiente')->sum('monto_solicitado');

        $retenido = max($recaudado - $liberado, 0);
        $disponible = max($recaudado - $liberado - $pendiente, 0);

        return [
            'recaudado' => $recaudado,
            'retenido' => $retenido,
            'liberado' => $liberado,
            'gastado' => $gastado,
            'pendiente' => $pendiente,
            'disponible' => $disponible,
        ];
    }

    private function decodeJson(?string $value): ?array
    {
        if (!$value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function getRecompensasPorProyecto($proyectos, $selectedProjectId, $estadoFiltro = null)
    {
        $query = Recompensa::with('proyecto')
            ->whereHas('proyecto', fn($q) => $q->where('creador_id', auth()->id()));

        if ($selectedProjectId) {
            $query->where('proyecto_id', $selectedProjectId);
        }

        $registros = $query->orderBy('id')->get();

        return $registros->map(function (Recompensa $r) {
            return [
                'id' => $r->id,
                'titulo' => $r->titulo,
                'monto' => $r->monto_minimo_aportacion,
                'descripcion' => $r->descripcion ?? 'Sin descripcion',
                'beneficios' => [],
                'limite' => $r->disponibilidad,
                'disponibles' => $r->disponibilidad,
                'orden' => $r->id,
                'estado' => $this->descripcionEsPausada($r->descripcion) ? 'pausado' : 'activo',
                'entrega' => 'Pendiente',
                'proyecto_id' => $r->proyecto_id,
                'proyecto' => $r->proyecto->titulo ?? 'Proyecto',
            ];
        })->when($estadoFiltro, fn($c) => $c->where('estado', $estadoFiltro));
    }

    private function authorizeRecompensa(Recompensa $recompensa, int $userId): void
    {
        $recompensa->loadMissing('proyecto');
        abort_unless(optional($recompensa->proyecto)->creador_id === $userId, 403);
    }

    private function descripcionEsPausada(?string $descripcion): bool
    {
        return str_starts_with((string) $descripcion, '[PAUSADO]');
    }

    private function aplicarEstadoEnDescripcion(?string $descripcion, string $estado): string
    {
        $desc = (string) $descripcion;
        $limpia = ltrim($desc);

        if ($this->descripcionEsPausada($limpia)) {
            $limpia = preg_replace('/^\\[PAUSADO\\]\\s*/', '', $limpia) ?? '';
        }

        return $estado === 'pausado' ? '[PAUSADO] ' . $limpia : $limpia;
    }

    private function resolveCategoriaNombre($categoriaId): ?string
    {
        if (!$categoriaId) {
            return null;
        }

        return ProyectoCategoria::find($categoriaId)?->nombre;
    }

    private function resolveModeloNombre($modeloId): ?string
    {
        if (!$modeloId) {
            return null;
        }

        return ProyectoModeloFinanciamiento::find($modeloId)?->nombre;
    }
}
