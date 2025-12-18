<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Proyecto;
use App\Models\Aportacion;
use App\Models\SolicitudDesembolso;
use App\Models\Pago;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\ActualizacionProyecto;
use App\Models\VerificacionSolicitud;
use App\Models\ProyectoCategoria;
use App\Models\ProyectoModeloFinanciamiento;
use App\Models\ReporteSospechoso;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $verifiedUsers = User::where('estado_verificacion', true)->count();
        $roleStats = Role::withCount('users')->orderBy('nombre_rol')->get();

        // Proyectos por estado
        $proyectosTotales = Proyecto::count();
        $proyectosPublicados = Proyecto::where('estado', 'publicado')->count();
        $proyectosRevision = Proyecto::whereIn('estado', ['borrador', 'pendiente', 'en_revision'])->count();
        $proyectosRiesgo = Proyecto::whereIn('estado', ['pausado', 'riesgo'])->count();

        // Finanzas globales
        $totalRecaudado = Aportacion::sum('monto');
        $solicitudes = SolicitudDesembolso::all();
        $fondosLiberados = $solicitudes->whereIn('estado', ['liberado', 'aprobado', 'pagado', 'gastado'])->sum('monto_solicitado');
        $fondosEscrow = max($totalRecaudado - $fondosLiberados, 0);
        $fondosGastados = Pago::sum('monto');

        // Pendientes críticos
        $pendientesKyc = VerificacionSolicitud::where('estado', 'pendiente')->count();
        $pendientesProyectos = $proyectosRevision;
        $pendientesDesembolsos = SolicitudDesembolso::where('estado', 'pendiente')->count();
        $reportesAbiertos = \App\Models\ReporteSospechoso::where('estado', 'pendiente')->count();

        // Riesgos
        $gastosObservados = Pago::whereIn('estado_auditoria', ['observado', 'rechazado'])->count();

        // Actividad reciente (simple timeline)
        $actividadReciente = collect([
            $this->actividadItem(Aportacion::with('proyecto')->latest()->first(), 'aporte'),
            $this->actividadItem(SolicitudDesembolso::with('proyecto')->latest()->first(), 'desembolso'),
            $this->actividadItem(\App\Models\ReporteSospechoso::with('proyecto')->latest()->first(), 'reporte'),
        ])->filter()->sortByDesc('created_at')->take(5);

        return view('admin.dashboard', [
            'totalUsers'    => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'roleStats'     => $roleStats,
            'projects' => [
                'total' => $proyectosTotales,
                'publicados' => $proyectosPublicados,
                'revision' => $proyectosRevision,
                'riesgo' => $proyectosRiesgo,
            ],
            'finanzas' => [
                'recaudado' => $totalRecaudado,
                'escrow' => $fondosEscrow,
                'liberado' => $fondosLiberados,
                'gastado' => $fondosGastados,
            ],
            'pendientes' => [
                'kyc' => $pendientesKyc,
                'proyectos' => $pendientesProyectos,
                'desembolsos' => $pendientesDesembolsos,
                'reportes' => $reportesAbiertos,
            ],
            'riesgos' => [
                'reportes' => $reportesAbiertos,
                'gastos_observados' => $gastosObservados,
                'proyectos_riesgo' => $proyectosRiesgo,
            ],
            'actividadReciente' => $actividadReciente,
        ]);
    }

    public function proyectosConfig(): View
    {
        $categorias = ProyectoCategoria::orderBy('nombre')->get();
        $modelos = ProyectoModeloFinanciamiento::orderBy('nombre')->get();

        return view('admin.modules.proyectos-config', compact('categorias', 'modelos'));
    }

    public function storeCategoriaProyecto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:proyecto_categorias,nombre'],
        ]);

        ProyectoCategoria::create($validated);

        return back()->with('status', 'Categoria creada.');
    }

    public function deleteCategoriaProyecto(ProyectoCategoria $categoria): RedirectResponse
    {
        $categoria->delete();

        return back()->with('status', 'Categoria eliminada.');
    }

    public function storeModeloFinanciamiento(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:proyecto_modelos_financiamiento,nombre'],
        ]);

        ProyectoModeloFinanciamiento::create($validated);

        return back()->with('status', 'Modelo de financiamiento creado.');
    }

    public function deleteModeloFinanciamiento(ProyectoModeloFinanciamiento $modelo): RedirectResponse
    {
        $modelo->delete();

        return back()->with('status', 'Modelo de financiamiento eliminado.');
    }

    public function roles(Request $request): View
    {
        $search = $request->query('q');
        $roleFilter = $request->query('role');
        $verificationFilter = $request->query('verificacion');

        $usersQuery = User::with('roles')->orderBy('name');

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nombre_completo', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $usersQuery->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('id', $roleFilter);
            });
        }

        if ($verificationFilter === 'verificado') {
            $usersQuery->where('estado_verificacion', true);
        } elseif ($verificationFilter === 'pendiente') {
            $usersQuery->where('estado_verificacion', false);
        }

        $users = $usersQuery->paginate(12)->withQueryString();
        $roles = Role::orderBy('nombre_rol')->get();

        return view('admin.modules.roles', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'verificationFilter' => $verificationFilter,
        ]);
    }

    public function showUser(User $user): View
    {
        $user->load(['roles', 'proyectosCreados' => function ($q) {
            $q->orderByDesc('created_at');
        }]);

        $aportaciones = $user->aportaciones()->with('proyecto')->orderByDesc('fecha_aportacion')->get();

        $stats = [
            'total_aportado' => $aportaciones->sum('monto'),
            'aportaciones' => $aportaciones->count(),
            'proyectos_apoyados' => $aportaciones->pluck('proyecto_id')->unique()->count(),
        ];

        $topProyectos = $aportaciones
            ->groupBy('proyecto_id')
            ->map(function ($group) {
                return [
                    'proyecto' => $group->first()->proyecto,
                    'total' => $group->sum('monto'),
                    'aportes' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5);

        $calificacion = DB::table('calificaciones')
            ->where('colaborador_id', $user->id)
            ->avg('puntaje');

        return view('admin.modules.usuarios-show', [
            'user' => $user,
            'aportaciones' => $aportaciones,
            'stats' => $stats,
            'topProyectos' => $topProyectos,
            'calificacion' => $calificacion,
        ]);
    }

    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $roleId = $validated['role_id'] ?? null;
        $user->roles()->sync($roleId ? [$roleId] : []);

        return redirect()
            ->route('admin.roles')
            ->with('status', "Rol del usuario {$user->name} actualizado.");
    }

    public function proyectos(Request $request): View
    {
        $search = $request->query('q');
        $estado = $request->query('estado');
        $categoria = $request->query('categoria');
        $modelo = $request->query('modelo');

        $proyectosQuery = Proyecto::with('creador')->orderByDesc('created_at');

        if ($search) {
            $proyectosQuery->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%")
                  ->orWhere('ubicacion_geografica', 'like', "%{$search}%");
            });
        }

        if ($estado) {
            $proyectosQuery->where('estado', $estado);
        }

        if ($categoria) {
            $proyectosQuery->where('categoria', $categoria);
        }

        if ($modelo) {
            $proyectosQuery->where('modelo_financiamiento', $modelo);
        }

        $proyectos = $proyectosQuery->paginate(10)->withQueryString();
        $categorias = ProyectoCategoria::orderBy('nombre')->pluck('nombre');
        $modelos = ProyectoModeloFinanciamiento::orderBy('nombre')->pluck('nombre');

        $recaudadoPorProyecto = Aportacion::selectRaw('proyecto_id, SUM(monto) as total')
            ->groupBy('proyecto_id')
            ->pluck('total', 'proyecto_id');
        $reportesPorProyecto = ReporteSospechoso::selectRaw('proyecto_id, COUNT(*) as total')
            ->groupBy('proyecto_id')
            ->pluck('total', 'proyecto_id');

        $estadoCounts = Proyecto::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $estadoResumen = [
            'borrador' => $estadoCounts['borrador'] ?? 0,
            'en_revision' => ($estadoCounts['pendiente'] ?? 0) + ($estadoCounts['en_revision'] ?? 0),
            'publicado' => $estadoCounts['publicado'] ?? 0,
            'pausado' => $estadoCounts['pausado'] ?? 0,
            'rechazado' => $estadoCounts['rechazado'] ?? 0,
        ];

        return view('admin.modules.proyectos', compact(
            'proyectos',
            'search',
            'estado',
            'categoria',
            'modelo',
            'categorias',
            'modelos',
            'estadoResumen',
            'recaudadoPorProyecto',
            'reportesPorProyecto'
        ));
    }

    public function showProyecto(Proyecto $proyecto): View
    {
        $proyecto->load('creador');

        $aporteQuery = Aportacion::where('proyecto_id', $proyecto->id);

        $topInversionistas = (clone $aporteQuery)
            ->select('colaborador_id', DB::raw('SUM(monto) as total'), DB::raw('COUNT(*) as aportes'))
            ->whereNotNull('colaborador_id')
            ->groupBy('colaborador_id')
            ->orderByDesc('total')
            ->with('colaborador')
            ->take(5)
            ->get();

        $aportacionesRecientes = (clone $aporteQuery)
            ->with('colaborador')
            ->orderByDesc('fecha_aportacion')
            ->take(8)
            ->get();

        $recaudadoAportes = (clone $aporteQuery)->sum('monto');
        $recaudadoProyecto = $proyecto->monto_recaudado ?? 0;
        $totalRecaudado = max($recaudadoAportes, $recaudadoProyecto);

        $stats = [
            'total_recaudado' => $totalRecaudado,
            'aportaciones' => (clone $aporteQuery)->count(),
            'colaboradores' => (clone $aporteQuery)->distinct('colaborador_id')->count('colaborador_id'),
        ];

        $solicitudes = SolicitudDesembolso::where('proyecto_id', $proyecto->id)->get();
        $fondosLiberados = $solicitudes->whereIn('estado', ['liberado','aprobado','pagado','gastado'])->sum('monto_solicitado');
        $pendiente = $solicitudes->where('estado', 'pendiente')->sum('monto_solicitado');
        $retenido = max($totalRecaudado - $fondosLiberados, 0);
        $fondosGastados = Pago::whereHas('solicitud', fn($q) => $q->where('proyecto_id', $proyecto->id))->sum('monto');
        $desembolsosTotal = $solicitudes->count();

        $reportesSospechosos = ReporteSospechoso::where('proyecto_id', $proyecto->id);
        $reportesAbiertos = (clone $reportesSospechosos)->where('estado', '<>', 'pendiente')->count();
        $reportesTotales = $reportesSospechosos->count();
        $pagosObservados = Pago::whereHas('solicitud', fn($q) => $q->where('proyecto_id', $proyecto->id))
            ->whereIn('estado_auditoria', ['observado', 'rechazado'])
            ->count();

        $transparencia = $fondosLiberados > 0 ? round(($fondosGastados / $fondosLiberados) * 100) : 0;

        $desembolsosRecientes = SolicitudDesembolso::where('proyecto_id', $proyecto->id)
            ->latest()
            ->take(5)
            ->get();
        $pagosRecientes = Pago::with('proveedor')
            ->whereHas('solicitud', fn($q) => $q->where('proyecto_id', $proyecto->id))
            ->latest('fecha_pago')
            ->take(5)
            ->get();
        $pagosTotal = Pago::whereHas('solicitud', fn($q) => $q->where('proyecto_id', $proyecto->id))->count();

        $cronograma = collect($proyecto->cronograma ?? [])
            ->filter(fn($h) => is_array($h))
            ->values();
        $hitosCumplidos = ActualizacionProyecto::where('proyecto_id', $proyecto->id)
            ->where('es_hito', true)
            ->count();
        $planificados = $cronograma->count();
        $progresoHitos = $planificados > 0
            ? min(100, (int) round(($hitosCumplidos / $planificados) * 100))
            : ($hitosCumplidos > 0 ? 100 : 0);

        return view('admin.modules.proyectos-show', [
            'proyecto' => $proyecto,
            'topInversionistas' => $topInversionistas,
            'aportacionesRecientes' => $aportacionesRecientes,
            'stats' => $stats,
            'fondos' => [
                'liberados' => $fondosLiberados,
                'retenidos' => $retenido,
                'pendiente' => $pendiente,
                'gastado' => $fondosGastados,
            ],
            'riesgos' => [
                'reportes_abiertos' => $reportesAbiertos,
                'reportes_totales' => $reportesTotales,
                'pagos_observados' => $pagosObservados,
                'transparencia' => $transparencia,
            ],
            'desembolsosRecientes' => $desembolsosRecientes,
            'pagosRecientes' => $pagosRecientes,
            'aportacionesTotal' => $stats['aportaciones'],
            'desembolsosTotal' => $desembolsosTotal,
            'pagosTotal' => $pagosTotal,
            'hitosCumplidos' => $hitosCumplidos,
            'planificados' => $planificados,
            'progresoHitos' => $progresoHitos,
        ]);
    }

    public function updateProyectoPublicacion(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:permitir,pausar'],
        ]);

        $proyecto->estado = $validated['accion'] === 'permitir' ? 'publicado' : 'pausado';
        $proyecto->save();

        return redirect()
            ->route('admin.proyectos.show', $proyecto)
            ->with('status', "Proyecto {$proyecto->estado}.");
    }

    public function proyectoGastos(Proyecto $proyecto): View
    {
        $proyecto->load('creador');

        $pagos = Pago::with(['proveedor', 'solicitud'])
            ->whereHas('solicitud', fn($q) => $q->where('proyecto_id', $proyecto->id))
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        $reporteProveedores = $pagos->groupBy('proveedor_id')->map(function ($items) {
            $proveedor = $items->first()->proveedor;
            return [
                'proveedor' => $proveedor?->nombre_proveedor ?? 'Sin proveedor',
                'total' => $items->sum('monto'),
                'pagos' => $items->count(),
                'conAdjuntos' => $items->filter(fn($p) => !empty($p->adjuntos))->count(),
            ];
        });

        $totales = [
            'pagos' => $pagos->count(),
            'monto' => $pagos->sum('monto'),
            'conAdjuntos' => $pagos->filter(fn($p) => !empty($p->adjuntos))->count(),
        ];

        return view('admin.modules.proyectos-gastos', compact('proyecto', 'pagos', 'reporteProveedores', 'totales'));
    }

    public function auditorias(): View
    {
        $desembolsosPendientes = SolicitudDesembolso::with('proyecto.creador')
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $pagosQuery = Pago::with(['proveedor', 'solicitud.proyecto'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id');
        $pagosAll = $pagosQuery->get();
        $pagosObservados = $pagosAll
            ->filter(fn ($p) => in_array($p->estado_auditoria, ['observado', 'rechazado']))
            ->take(5);

        $reportesPendientes = ReporteSospechoso::with(['proyecto', 'colaborador'])
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $proyectosRevision = Proyecto::with('creador')
            ->where('estado', 'borrador')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $gastosConComprobante = $pagosAll->filter(fn ($p) => !empty($p->adjuntos))->count();
        $gastosSinComprobante = $pagosAll->filter(fn ($p) => empty($p->adjuntos))->count();
        $gastosEnRevision = $pagosAll->where('estado_auditoria', 'pendiente')->count();
        $gastosValidados = $pagosAll->where('estado_auditoria', 'aprobado')->count();
        $gastosTotales = $pagosAll->count();

        $reportesCerrados30 = ReporteSospechoso::where('estado', '!=', 'pendiente')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $actividadTimeline = collect();
        $pagosTimeline = $pagosAll
            ->sortByDesc(fn ($p) => $p->updated_at ?? $p->created_at)
            ->take(5)
            ->map(function ($pago) {
                $proyecto = $pago->solicitud->proyecto->titulo ?? 'Proyecto';
                $estado = ucfirst($pago->estado_auditoria ?? 'pendiente');
                return [
                    'mensaje' => "Pago de US$ " . number_format($pago->monto, 2) . " en {$proyecto} marcado como {$estado}",
                    'timestamp' => optional($pago->updated_at ?? $pago->fecha_pago)->format('d/m/Y H:i'),
                ];
            });

        $reportesTimeline = ReporteSospechoso::with(['proyecto', 'colaborador'])
            ->orderByDesc('updated_at')
            ->take(3)
            ->get()
            ->map(function ($rep) {
                $proyecto = $rep->proyecto->titulo ?? 'Proyecto';
                $estado = ucfirst($rep->estado ?? 'pendiente');
                return [
                    'mensaje' => "Reporte #{$rep->id} en {$proyecto} ({$estado})",
                    'timestamp' => optional($rep->updated_at)->format('d/m/Y H:i'),
                ];
            });

        $actividadTimeline = $actividadTimeline
            ->merge($pagosTimeline)
            ->merge($reportesTimeline)
            ->sortByDesc('timestamp')
            ->values()
            ->take(6);

        $auditLog = collect();
        $accionesPagos = $pagosAll
            ->filter(fn ($p) => in_array($p->estado_auditoria, ['aprobado', 'rechazado', 'observado']))
            ->sortByDesc(fn ($p) => $p->updated_at ?? $p->created_at)
            ->take(5)
            ->map(function ($pago) {
                $proyecto = $pago->solicitud->proyecto->titulo ?? 'Proyecto';
                $estado = ucfirst($pago->estado_auditoria ?? 'pendiente');
                return [
                    'usuario' => 'Auditor',
                    'accion' => "{$estado} pago en {$proyecto}",
                    'fecha' => optional($pago->updated_at ?? $pago->fecha_pago)->format('d/m/Y H:i'),
                ];
            });

        $accionesDesembolsos = SolicitudDesembolso::with('proyecto')
            ->whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado', 'rechazado'])
            ->orderByDesc('updated_at')
            ->take(3)
            ->get()
            ->map(function ($sol) {
                $proyecto = $sol->proyecto->titulo ?? 'Proyecto';
                $estado = ucfirst($sol->estado ?? 'pendiente');
                return [
                    'usuario' => 'Admin/Auditor',
                    'accion' => "{$estado} desembolso {$proyecto}",
                    'fecha' => optional($sol->updated_at ?? $sol->created_at)->format('d/m/Y H:i'),
                ];
            });

        $auditLog = $auditLog->merge($accionesPagos)->merge($accionesDesembolsos)->take(6);

        $resumen = [
            'desembolsos_pendientes' => SolicitudDesembolso::where('estado', 'pendiente')->count(),
            'pagos_observados' => $pagosAll->whereIn('estado_auditoria', ['observado', 'rechazado'])->count(),
            'reportes_abiertos' => ReporteSospechoso::where('estado', 'pendiente')->count(),
            'proyectos_revision' => Proyecto::where('estado', 'borrador')->count(),
            'reportes_cerrados_30d' => $reportesCerrados30,
            'incidencias_graves' => $pagosAll->whereIn('estado_auditoria', ['observado', 'rechazado'])->count(),
            'gastos_validados' => $gastosValidados,
            'gastos_totales' => $gastosTotales,
            'gastos_con_comprobante' => $gastosConComprobante,
            'gastos_sin_comprobante' => $gastosSinComprobante,
            'gastos_en_revision' => $gastosEnRevision,
        ];

        return view('admin.modules.auditorias', compact(
            'desembolsosPendientes',
            'pagosObservados',
            'reportesPendientes',
            'proyectosRevision',
            'resumen',
            'actividadTimeline',
            'auditLog'
        ));
    }

    public function reportesSospechosos(Request $request): View
    {
        $estado = $request->query('estado');
        $q = $request->query('q');

        $reportesQuery = ReporteSospechoso::with(['proyecto', 'colaborador'])
            ->orderByDesc('created_at');

        if ($estado) {
            $reportesQuery->where('estado', $estado);
        }

        if ($q) {
            $reportesQuery->where(function ($sub) use ($q) {
                $sub->where('id', $q)
                    ->orWhereHas('proyecto', fn ($inner) => $inner->where('titulo', 'like', "%{$q}%"))
                    ->orWhereHas('colaborador', fn ($inner) => $inner->where('nombre_completo', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%"))
                    ->orWhere('motivo', 'like', "%{$q}%");
            });
        }

        /** @var LengthAwarePaginator $reportes */
        $reportes = $reportesQuery->paginate(12)->withQueryString();
        $estados = ReporteSospechoso::select('estado')->distinct()->pluck('estado')->filter()->values();

        $totales = [
            'abiertos' => ReporteSospechoso::where('estado', 'pendiente')->count(),
            'total' => ReporteSospechoso::count(),
        ];

        return view('admin.modules.reportes-sospechosos', compact(
            'reportes',
            'estado',
            'q',
            'estados',
            'totales'
        ));
    }

    public function updateReporteSospechosoEstado(Request $request, ReporteSospechoso $reporte): RedirectResponse
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar'],
            'respuesta' => ['required', 'string', 'min:20', 'max:500'],
        ]);

        $nuevoEstado = $validated['accion'] === 'aprobar' ? 'aprobado' : 'rechazado';
        $reporte->estado = $nuevoEstado;
        $reporte->respuesta = $validated['respuesta'];
        $reporte->save();

        return back()->with('status', "Reporte #{$reporte->id} {$nuevoEstado}.");
    }

    public function finanzas(): View
    {
        $totRecaudado = Aportacion::sum('monto');
        $solicitudes = SolicitudDesembolso::all();
        $liberado = $solicitudes->whereIn('estado', ['liberado', 'aprobado', 'pagado', 'gastado'])->sum('monto_solicitado');
        $pendiente = $solicitudes->where('estado', 'pendiente')->sum('monto_solicitado');
        $retenido = max($totRecaudado - $liberado, 0);
        $gastado = Pago::sum('monto');
        $disponible = max($totRecaudado - $liberado - $pendiente, 0);

        $stats = [
            'recaudado' => $totRecaudado,
            'retenido' => $retenido,
            'liberado' => $liberado,
            'gastado' => $gastado,
            'pendiente' => $pendiente,
            'disponible' => $disponible,
        ];

        return view('admin.modules.finanzas', compact('stats'));
    }

    public function proveedores(): View
    {
        $search = request()->query('q');

        $proyectos = Proyecto::orderBy('titulo')->get(['id','titulo','creador_id']);
        $creadores = User::orderBy('name')->get(['id','name','nombre_completo']);

        $proveedoresQuery = Proveedor::with(['proyecto', 'creador'])
            ->withAvg('historiales as calificacion_promedio', 'calificacion')
            ->latest();

        if ($search) {
            $proveedoresQuery->where(function ($q) use ($search) {
                $q->where('nombre_proveedor', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%")
                  ->orWhere('info_contacto', 'like', "%{$search}%");
            });
        }

        $proveedores = $proveedoresQuery->paginate(12)->withQueryString();
        $stats = [
            'total' => Proveedor::count(),
            'conProyecto' => Proveedor::whereNotNull('proyecto_id')->count(),
            'calificacionPromedio' => round((float) \App\Models\ProveedorHistorial::avg('calificacion'), 2),
        ];

        return view('admin.modules.proveedores', compact('proveedores', 'proyectos', 'creadores', 'search', 'stats'));
    }

    public function showProveedor(Proveedor $proveedor): View
    {
        $proveedor->load([
            'proyecto',
            'creador',
            'historiales' => fn($q) => $q->orderByDesc('fecha_entrega')->orderByDesc('created_at'),
        ]);

        $calificacionPromedio = $proveedor->historiales()
            ->whereNotNull('calificacion')
            ->avg('calificacion');

        $pagos = Pago::with(['solicitud.proyecto'])
            ->where('proveedor_id', $proveedor->id)
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        $pagoStats = [
            'total' => $pagos->sum('monto'),
            'count' => $pagos->count(),
            'conAdjuntos' => $pagos->filter(fn ($p) => !empty($p->adjuntos))->count(),
            'pendientes' => $pagos->where('estado_auditoria', 'pendiente')->count(),
            'aprobados' => $pagos->where('estado_auditoria', 'aprobado')->count(),
            'observados' => $pagos->whereIn('estado_auditoria', ['observado', 'rechazado'])->count(),
        ];

        return view('admin.modules.proveedores-show', compact(
            'proveedor',
            'calificacionPromedio',
            'pagos',
            'pagoStats'
        ));
    }


    public function finanzasProyectos(): View
    {
        $proyectos = Proyecto::with('creador')->get();

        $recaudado = Aportacion::selectRaw('proyecto_id, SUM(monto) as total')->groupBy('proyecto_id')->pluck('total', 'proyecto_id');
        $solicitudes = SolicitudDesembolso::selectRaw("
            proyecto_id,
            SUM(monto_solicitado) as total,
            SUM(CASE WHEN estado IN ('liberado','aprobado','pagado','gastado') THEN monto_solicitado ELSE 0 END) as liberado,
            SUM(CASE WHEN estado = 'pendiente' THEN monto_solicitado ELSE 0 END) as pendiente
        ")->groupBy('proyecto_id')->get()->keyBy('proyecto_id');

        $filas = $proyectos->map(function ($p) use ($recaudado, $solicitudes) {
            $r = $recaudado[$p->id] ?? 0;
            $s = $solicitudes[$p->id] ?? null;
            $lib = $s->liberado ?? 0;
            $pen = $s->pendiente ?? 0;
            $retenido = max($r - $lib, 0);
            return [
                'proyecto' => $p,
                'recaudado' => $r,
                'retenido' => $retenido,
                'liberado' => $lib,
                'pendiente' => $pen,
            ];
        });

        return view('admin.modules.finanzas-proyectos', compact('filas'));
    }

    public function finanzasSolicitudes(Request $request): View
    {
        $estado = $request->query('estado');
        $q = $request->query('q');

        $query = SolicitudDesembolso::with(['proyecto.creador'])
            ->orderByDesc('created_at');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($q) {
            $query->whereHas('proyecto', function ($sub) use ($q) {
                $sub->where('titulo', 'like', "%{$q}%");
            });
        }

        $solicitudes = $query->paginate(12)->withQueryString();
        $totales = [
            'solicitado' => $query->clone()->sum('monto_solicitado'),
            'aprobado' => SolicitudDesembolso::whereIn('estado', ['aprobado','liberado','pagado','gastado'])->sum('monto_solicitado'),
        ];

        return view('admin.modules.finanzas-solicitudes', compact('solicitudes', 'estado', 'q', 'totales'));
    }

    public function updateSolicitudFondos(Request $request, SolicitudDesembolso $solicitud): RedirectResponse
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:liberar,pausar,reintentar'],
            'justificacion_admin' => ['nullable', 'string'],
        ]);

        $estado = match ($validated['accion']) {
            'liberar' => 'liberado',
            'pausar' => 'pausado',
            'reintentar' => 'pendiente',
        };

        $solicitud->estado = $estado;
        $solicitud->estado_admin = $validated['accion'];
        $solicitud->justificacion_admin = $validated['justificacion_admin'] ?? null;
        $solicitud->save();

        return redirect()->back()->with('status', 'Solicitud actualizada manualmente.');
    }

    public function verificaciones(Request $request): View
    {
        $estado = $request->query('estado');
        $query = VerificacionSolicitud::with('user')->latest();
        if ($estado) {
            $query->where('estado', $estado);
        }
        $solicitudes = $query->paginate(12)->withQueryString();

        return view('admin.modules.verificaciones', compact('solicitudes', 'estado'));
    }

    public function updateVerificacion(Request $request, VerificacionSolicitud $solicitud): RedirectResponse
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar'],
            'nota' => ['nullable', 'string'],
        ]);

        $solicitud->estado = $validated['accion'] === 'aprobar' ? 'aprobada' : 'rechazada';
        $solicitud->nota = $validated['nota'] ?? null;
        $solicitud->save();

        if ($validated['accion'] === 'aprobar') {
            $solicitud->user->estado_verificacion = true;
            $solicitud->user->save();
        }

        return redirect()->route('admin.verificaciones')->with('status', 'Solicitud actualizada.');
    }

    public function verificacionAdjunto(VerificacionSolicitud $solicitud, string $tipo)
    {
        $allowed = ['documento_frontal', 'documento_reverso', 'selfie'];
        abort_unless(in_array($tipo, $allowed, true), 404);

        $path = $solicitud->adjuntos[$tipo] ?? null;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function exportFondosRetenidos()
    {
        $proyectos = Proyecto::with('creador')->get();
        $recaudado = Aportacion::selectRaw('proyecto_id, SUM(monto) as total')->groupBy('proyecto_id')->pluck('total', 'proyecto_id');
        $solicitudes = SolicitudDesembolso::selectRaw("
            proyecto_id,
            SUM(CASE WHEN estado IN ('liberado','aprobado','pagado','gastado') THEN monto_solicitado ELSE 0 END) as liberado
        ")->groupBy('proyecto_id')->get()->keyBy('proyecto_id');

        $rows = [];
        foreach ($proyectos as $p) {
            $rec = max($recaudado[$p->id] ?? 0, $p->monto_recaudado ?? 0);
            $lib = $solicitudes[$p->id]->liberado ?? 0;
            $retenido = max($rec - $lib, 0);
            $rows[] = [
                $p->id,
                $p->titulo,
                $p->creador->nombre_completo ?? $p->creador->name ?? 'N/D',
                $rec,
                $lib,
                $retenido,
            ];
        }

        $header = ['Proyecto ID', 'Proyecto', 'Creador', 'Recaudado', 'Liberado', 'Retenido'];
        return $this->streamExcel('fondos_retenidos.xls', $header, $rows);
    }

    public function exportFondosLiberados()
    {
        $solicitudes = SolicitudDesembolso::with('proyecto.creador')
            ->whereIn('estado', ['aprobado','liberado','pagado','gastado'])
            ->orderByDesc('created_at')
            ->get();

        $rows = $solicitudes->map(function ($s) {
            return [
                $s->id,
                $s->proyecto->titulo ?? 'Proyecto',
                $s->proyecto->creador->nombre_completo ?? $s->proyecto->creador->name ?? 'N/D',
                $s->hito ?? 'N/D',
                $s->estado,
                $s->monto_solicitado,
                $s->created_at?->format('Y-m-d H:i'),
            ];
        })->all();

        $header = ['Solicitud ID', 'Proyecto', 'Creador', 'Hito', 'Estado', 'Monto', 'Creada'];
        return $this->streamExcel('fondos_liberados.xls', $header, $rows);
    }

    public function exportRecaudacionMensual()
    {
        $rows = Aportacion::selectRaw("DATE_FORMAT(fecha_aportacion, '%Y-%m') as periodo, SUM(monto) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->map(fn($r) => [$r->periodo, $r->total])
            ->all();

        $header = ['Periodo (YYYY-MM)', 'Total'];
        return $this->streamExcel('recaudacion_mensual.xls', $header, $rows);
    }

    public function exportRecaudacionCategoria()
    {
        $rows = Proyecto::selectRaw('categoria, SUM(monto_recaudado) as rec_proyecto')
            ->leftJoin('aportaciones', 'proyectos.id', '=', 'aportaciones.proyecto_id')
            ->selectRaw('COALESCE(SUM(aportaciones.monto), 0) as rec_aportes')
            ->groupBy('categoria')
            ->get()
            ->map(function ($r) {
                $rec = max($r->rec_proyecto ?? 0, $r->rec_aportes ?? 0);
                return [$r->categoria ?? 'Sin categoria', $rec];
            })
            ->all();

        $header = ['Categoria', 'Total recaudado'];
        return $this->streamExcel('recaudacion_categoria.xls', $header, $rows);
    }

    private function streamExcel(string $filename, array $header, array $rows)
    {
        $callback = function () use ($header, $rows) {
            $escape = function ($value) {
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            };
            echo "<table border='1'>";
            echo "<tr>";
            foreach ($header as $col) {
                echo "<th>" . $escape($col) . "</th>";
            }
            echo "</tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . $escape($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        };

        return Response::streamDownload($callback, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    private function actividadItem($model, string $tipo): ?array
    {
        if (!$model) {
            return null;
        }

        return array_merge($model->only(['id', 'created_at']), ['tipo' => $tipo]);
    }

    public function auditoriasActividad(Request $request): View
    {
        $q = $request->query('q');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $from = $desde ? Carbon::parse($desde)->startOfDay() : null;
        $to = $hasta ? Carbon::parse($hasta)->endOfDay() : null;

        $pagosQuery = Pago::with(['solicitud.proyecto', 'proveedor'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($from) {
            $pagosQuery->where('updated_at', '>=', $from);
        }
        if ($to) {
            $pagosQuery->where('updated_at', '<=', $to);
        }
        if ($q) {
            $pagosQuery->where(function ($sub) use ($q) {
                $sub->where('concepto', 'like', "%{$q}%")
                    ->orWhereHas('proveedor', fn($p) => $p->where('nombre_proveedor', 'like', "%{$q}%"))
                    ->orWhereHas('solicitud.proyecto', fn($p) => $p->where('titulo', 'like', "%{$q}%"));
            });
        }
        $pagosEventos = $pagosQuery->take(200)->get()->map(function (Pago $pago) {
            $proyecto = $pago->solicitud->proyecto->titulo ?? 'Proyecto';
            $estado = ucfirst($pago->estado_auditoria ?? 'pendiente');
            return [
                'tipo' => 'pago',
                'mensaje' => "Pago de US$ " . number_format($pago->monto, 2) . " en {$proyecto} marcado como {$estado}",
                'timestamp' => optional($pago->updated_at ?? $pago->fecha_pago)->format('Y-m-d H:i'),
                'link' => route('admin.gastos', ['q' => $pago->id]),
            ];
        });

        $reportesQuery = ReporteSospechoso::with(['proyecto', 'colaborador'])
            ->orderByDesc('updated_at');

        if ($from) {
            $reportesQuery->where('updated_at', '>=', $from);
        }
        if ($to) {
            $reportesQuery->where('updated_at', '<=', $to);
        }
        if ($q) {
            $reportesQuery->where(function ($sub) use ($q) {
                $sub->where('motivo', 'like', "%{$q}%")
                    ->orWhereHas('proyecto', fn($p) => $p->where('titulo', 'like', "%{$q}%"))
                    ->orWhereHas('colaborador', fn($p) => $p->where('nombre_completo', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%"));
            });
        }
        $reportesEventos = $reportesQuery->take(200)->get()->map(function (ReporteSospechoso $rep) {
            $proyecto = $rep->proyecto->titulo ?? 'Proyecto';
            $estado = ucfirst($rep->estado ?? 'pendiente');
            return [
                'tipo' => 'reporte',
                'mensaje' => "Reporte #{$rep->id} en {$proyecto} ({$estado})",
                'timestamp' => optional($rep->updated_at)->format('Y-m-d H:i'),
                'link' => route('admin.reportes', ['q' => $rep->id]),
            ];
        });

        $eventos = $pagosEventos->merge($reportesEventos)
            ->filter(fn($e) => !empty($e['timestamp']))
            ->sortByDesc('timestamp')
            ->values();

        return view('admin.modules.auditorias-actividad', compact('eventos', 'q', 'desde', 'hasta'));
    }

    public function gastos(Request $request): View
    {
        $estadoAuditoria = $request->query('estado_auditoria');
        $q = $request->query('q');

        $pagosQuery = Pago::with(['proveedor', 'solicitud.proyecto'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id');

        if ($estadoAuditoria) {
            $pagosQuery->where('estado_auditoria', $estadoAuditoria);
        }

        if ($q) {
            $pagosQuery->where(function ($sub) use ($q) {
                $sub->where('concepto', 'like', "%{$q}%")
                    ->orWhereHas('proveedor', fn($p) => $p->where('nombre_proveedor', 'like', "%{$q}%"))
                    ->orWhereHas('solicitud.proyecto', fn($p) => $p->where('titulo', 'like', "%{$q}%"));
            });
        }

        $pagos = $pagosQuery->paginate(15)->withQueryString();

        $stats = [
            'total' => Pago::count(),
            'conAdjuntos' => Pago::whereNotNull('adjuntos')->count(),
            'pendientes' => Pago::where('estado_auditoria', 'pendiente')->count(),
            'aprobados' => Pago::where('estado_auditoria', 'aprobado')->count(),
            'observados' => Pago::whereIn('estado_auditoria', ['observado', 'rechazado'])->count(),
        ];

        $estados = Pago::select('estado_auditoria')->distinct()->pluck('estado_auditoria')->filter()->values();

        return view('admin.modules.gastos', compact('pagos', 'estadoAuditoria', 'q', 'stats', 'estados'));
    }
}
