<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudDesembolso;
use App\Models\Pago;
use App\Models\VerificacionSolicitud;
use App\Models\Proyecto;
use App\Models\ProyectoCategoria;
use App\Models\ActualizacionProyecto;
use App\Models\ReporteSospechoso;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuditorController extends Controller
{
    public function index()
    {
        $kpis = [
            'solicitudes_pendientes' => SolicitudDesembolso::where('estado', 'pendiente')->count(),
            'solicitudes_aprobadas' => SolicitudDesembolso::whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado'])->count(),
            'pagos_registrados' => Pago::count(),
            'kyc_pendientes' => VerificacionSolicitud::where('estado', 'pendiente')->count(),
            'reportes_pendientes' => ReporteSospechoso::where('estado', 'pendiente')->count(),
        ];

        $solicitudesPendientes = SolicitudDesembolso::with('proyecto')
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $pagosRecientes = Pago::with(['proveedor', 'solicitud.proyecto'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $verificacionesPendientes = VerificacionSolicitud::with('user')
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $reportesPendientes = ReporteSospechoso::with('proyecto')
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $proyectosActivos = Proyecto::orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('auditor.dashboard', compact(
            'kpis',
            'solicitudesPendientes',
            'pagosRecientes',
            'verificacionesPendientes',
            'reportesPendientes',
            'proyectosActivos'
        ));
    }

    public function comprobantes()
    {
        $estado = request()->query('estado');
        $q = request()->query('q');

        $pagosQuery = Pago::with(['proveedor', 'solicitud.proyecto'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id');

        if ($estado) {
            $pagosQuery->whereHas('solicitud', function ($sub) use ($estado) {
                $sub->where('estado', $estado);
            });
        }

        if ($q) {
            $pagosQuery->where(function ($sub) use ($q) {
                $sub->where('concepto', 'like', "%{$q}%")
                    ->orWhereHas('proveedor', function ($p) use ($q) {
                        $p->where('nombre_proveedor', 'like', "%{$q}%");
                    })
                    ->orWhereHas('solicitud.proyecto', function ($p) use ($q) {
                        $p->where('titulo', 'like', "%{$q}%");
                    });
            });
        }

        $pagos = $pagosQuery->paginate(15)->withQueryString();
        $estados = SolicitudDesembolso::select('estado')->distinct()->pluck('estado')->filter()->values();

        return view('auditor.modules.comprobantes', compact('pagos', 'estado', 'q', 'estados'));
    }

    public function showComprobante(Pago $pago)
    {
        $pago->load(['proveedor', 'solicitud.proyecto']);
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

        return view('auditor.modules.comprobantes-show', compact('pago', 'adjuntos'));
    }

    public function updateComprobanteEstado(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar,observar'],
            'nota' => ['nullable', 'string', 'max:2000'],
        ]);

        $nuevoEstado = match ($validated['accion']) {
            'aprobar' => 'aprobado',
            'rechazar' => 'rechazado',
            'observar' => 'observado',
        };

        if ($nuevoEstado === 'rechazado' && empty($validated['nota'])) {
            return back()->withErrors(['nota' => 'Agrega una nota para rechazar el comprobante.'])->withInput();
        }

        $pago->estado_auditoria = $nuevoEstado;
        $pago->nota_auditoria = $validated['nota'] ?? null;
        $pago->save();

        return redirect()
            ->route('auditor.comprobantes.show', $pago)
            ->with('status', "Comprobante {$nuevoEstado}.");
    }

    public function desembolsos()
    {
        $estado = request()->query('estado');
        $q = request()->query('q');

        $query = SolicitudDesembolso::with('proyecto')
            ->orderByDesc('created_at');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($q) {
            $query->whereHas('proyecto', function ($sub) use ($q) {
                $sub->where('titulo', 'like', "%{$q}%");
            });
        }

        $solicitudes = $query->paginate(15)->withQueryString();
        $estados = SolicitudDesembolso::select('estado')->distinct()->pluck('estado')->filter()->values();

        return view('auditor.modules.desembolsos', compact('solicitudes', 'estado', 'q', 'estados'));
    }

    public function showDesembolso(SolicitudDesembolso $solicitud)
    {
        $solicitud->load('proyecto');
        $adjuntos = collect($solicitud->adjuntos ?? [])
            ->filter()
            ->map(function ($path) {
                $normalized = ltrim(preg_replace('/^public\\//', '', $path), '/');
                if (Storage::disk('public')->exists($normalized)) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }
                if (file_exists(public_path('storage/' . $normalized))) {
                    return ['path' => $path, 'url' => asset('storage/' . $normalized)];
                }
                return ['path' => $path, 'url' => Storage::url($path)];
            });

        return view('auditor.modules.desembolsos-show', compact('solicitud', 'adjuntos'));
    }

    public function updateDesembolsoEstado(Request $request, SolicitudDesembolso $solicitud)
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar'],
            'nota' => ['nullable', 'string', 'max:2000'],
        ]);

        $nuevoEstado = $validated['accion'] === 'aprobar' ? 'aprobado' : 'rechazado';
        if ($nuevoEstado === 'rechazado' && empty($validated['nota'])) {
            return back()->withErrors(['nota' => 'Agrega una nota para rechazar la solicitud.'])->withInput();
        }

        $solicitud->estado = $nuevoEstado;
        $solicitud->estado_admin = $validated['accion'];
        $solicitud->justificacion_admin = $validated['nota'] ?? null;
        $solicitud->save();

        return redirect()
            ->route('auditor.desembolsos.show', $solicitud)
            ->with('status', "Solicitud {$nuevoEstado}.");
    }

    public function reportes(Request $request)
    {
        $q = $request->query('q');
        $estado = $request->query('estado');

        $reportesQuery = ReporteSospechoso::with(['proyecto', 'colaborador'])
            ->orderByDesc('created_at')
            ->when($estado, fn($query) => $query->where('estado', $estado))
            ->when($q, fn($query) => $query->where(function ($sub) use ($q) {
                $sub->whereHas('proyecto', fn($inner) => $inner->where('titulo', 'like', "%{$q}%"))
                    ->orWhereHas('colaborador', fn($inner) => $inner->where('nombre_completo', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%"))
                    ->orWhere('motivo', 'like', "%{$q}%");
            }));

        $reportesColab = $reportesQuery->paginate(12)->withQueryString();
        $estados = ReporteSospechoso::select('estado')->distinct()->pluck('estado')->filter()->values();

        return view('auditor.modules.reportes', compact('reportesColab', 'q', 'estado', 'estados'));
    }

    public function updateReporteEstado(Request $request, ReporteSospechoso $reporte)
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar'],
            'respuesta' => ['required', 'string', 'min:20', 'max:500'],
        ]);

        $nuevoEstado = $validated['accion'] === 'aprobar' ? 'aprobado' : 'rechazado';
        $reporte->estado = $nuevoEstado;
        $reporte->respuesta = $validated['respuesta'] ?? null;
        $reporte->save();

        return back()->with('status', "Reporte {$nuevoEstado}.");
    }
    public function hitos()
    {
        $q = request()->query('q');
        $categoria = request()->query('categoria');

        $proyectos = Proyecto::withCount(['hitos' => function ($q2) {
                $q2->where('es_hito', true);
            }])
            ->when($q, fn($query) => $query->where('titulo', 'like', "%{$q}%"))
            ->when($categoria, fn($query) => $query->where('categoria', $categoria))
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        $categorias = ProyectoCategoria::orderBy('nombre')->pluck('nombre')
            ->merge(Proyecto::select('categoria')->distinct()->pluck('categoria'))
            ->filter()
            ->unique()
            ->values();

        $proyectos->getCollection()->transform(function (Proyecto $proyecto) {
            $portadaUrl = null;
            if ($proyecto->imagen_portada) {
                $normalized = ltrim(preg_replace('/^public\\//', '', $proyecto->imagen_portada), '/');
                if (Storage::disk('public')->exists($normalized)) {
                    $portadaUrl = asset('storage/' . $normalized);
                } elseif (file_exists(public_path($proyecto->imagen_portada))) {
                    $portadaUrl = asset($proyecto->imagen_portada);
                }
            }

            $cronograma = collect($proyecto->cronograma ?? [])->filter(fn($h) => is_array($h))->values();
            $planificados = $cronograma->count();
            $reportados = (int) ($proyecto->hitos_count ?? 0);
            $basePlan = $planificados ?: max($reportados, 1);
            $progresoHitos = $basePlan ? min(100, (int) round(($reportados / $basePlan) * 100)) : 0;

            $proyecto->portada_url = $portadaUrl;
            $proyecto->cronograma_planificados = $planificados;
            $proyecto->hitos_reportados = $reportados;
            $proyecto->progreso_hitos = $progresoHitos;

            return $proyecto;
        });

        return view('auditor.modules.hitos', compact('proyectos', 'q', 'categoria', 'categorias'));
    }

    public function hitosProyecto(Request $request, Proyecto $proyecto)
    {
        $q = $request->query('q');

        $hitos = ActualizacionProyecto::where('proyecto_id', $proyecto->id)
            ->where('es_hito', true)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('titulo', 'like', "%{$q}%")
                        ->orWhere('contenido', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('fecha_publicacion')
            ->paginate(12)
            ->withQueryString();

        return view('auditor.modules.hitos-proyecto', compact('proyecto', 'hitos', 'q'));
    }

    public function proyectos()
    {
        $q = request()->query('q');
        $estado = request()->query('estado');

        $proyectos = Proyecto::withCount('aportaciones')
            ->when($q, fn($query) => $query->where('titulo', 'like', "%{$q}%"))
            ->when($estado, fn($query) => $query->where('estado', $estado))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $estadosPublicacion = Proyecto::select('estado')->distinct()->pluck('estado')->filter()->values();

        return view('auditor.modules.proyectos', compact('proyectos', 'q', 'estado', 'estadosPublicacion'));
    }

    public function showProyecto(Proyecto $proyecto)
    {
        $portadaUrl = null;
        if ($proyecto->imagen_portada) {
            $normalized = ltrim(preg_replace('/^public\\//', '', $proyecto->imagen_portada), '/');
            if (Storage::disk('public')->exists($normalized)) {
                $portadaUrl = asset('storage/' . $normalized);
            } elseif (file_exists(public_path($proyecto->imagen_portada))) {
                $portadaUrl = asset($proyecto->imagen_portada);
            }
        }

        $cronograma = collect($proyecto->cronograma ?? [])
            ->filter(fn($h) => is_array($h))
            ->values();

        return view('auditor.modules.proyectos-show', compact('proyecto', 'portadaUrl', 'cronograma'));
    }

    public function updateProyectoPublicacion(Request $request, Proyecto $proyecto)
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:permitir,pausar'],
        ]);

        $proyecto->estado = $validated['accion'] === 'permitir' ? 'publicado' : 'pausado';
        $proyecto->save();

        return redirect()
            ->route('auditor.proyectos.show', $proyecto)
            ->with('status', "Proyecto {$proyecto->estado}.");
    }
}
