@extends('creator.layouts.panel')

@section('title', 'Historial de solicitudes')
@section('active', 'fondos')

@section('content')
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-8 shadow-2xl ring-1 ring-indigo-500/10 space-y-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Proyectos</p>
                <h2 class="text-2xl font-bold text-white">Solicitudes de desembolso</h2>
                <p class="text-sm text-zinc-400">Consulta todas las solicitudes con sus hitos, estados y adjuntos.</p>
            </div>
            <div class="text-xs text-zinc-300">
                <span class="rounded-full bg-white/5 px-3 py-1">Total proyectos: {{ $proyectos->count() }}</span>
            </div>
        </div>

        <form method="GET" action="{{ route('creador.fondos.historial') }}" class="grid gap-3 sm:grid-cols-[1fr,auto] sm:items-end">
            <div>
                <label class="text-xs text-zinc-400">Proyecto</label>
                <select name="proyecto" class="mt-1 w-full appearance-none rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                    @forelse ($proyectos as $proyecto)
                        <option value="{{ $proyecto->id }}" @selected($selectedProjectId == $proyecto->id)>{{ $proyecto->titulo }}</option>
                    @empty
                        <option value="">Sin proyectos disponibles</option>
                    @endforelse
                </select>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                Ver historial
            </button>
        </form>
    </section>

    @if ($proyectos->isEmpty())
        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-8 text-sm text-zinc-300 shadow-2xl">
            No tienes proyectos creados aun. <a class="text-indigo-300 underline" href="{{ route('creador.proyectos') }}">Crea un proyecto</a> para gestionar solicitudes.
        </section>
    @else
        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-indigo-500/10 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Historial</p>
                    <h3 class="text-lg font-bold text-white">Solicitudes del proyecto</h3>
                    <p class="text-sm text-zinc-400">Incluye monto, hito, estado, auditoría y adjuntos.</p>
                </div>
                <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-zinc-200">Total: {{ $solicitudes->count() }}</span>
            </div>

            <div class="space-y-3">
                @php
                    $estadoBadges = [
                        'pendiente' => 'bg-amber-500/15 text-amber-100 border border-amber-400/30',
                        'aprobado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                        'liberado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                        'pagado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                        'rechazado' => 'bg-red-500/15 text-red-100 border border-red-400/30',
                        'gastado' => 'bg-sky-500/15 text-sky-100 border border-sky-400/30',
                    ];
                @endphp
                @forelse ($solicitudes as $solicitud)
                    @php
                        $badge = $estadoBadges[$solicitud->estado] ?? 'bg-white/10 text-white border border-white/20';
                        $badgeAdmin = $solicitud->estado_admin ? ($estadoBadges[$solicitud->estado_admin] ?? 'bg-white/10 text-white border border-white/20') : null;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner ring-1 ring-indigo-500/10 space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $solicitud->hito ?? 'Hito no asignado' }}</p>
                                <p class="text-xs text-zinc-400">{{ $solicitud->created_at?->format('d/m/Y H:i') }} - Fecha estimada: {{ $solicitud->fecha_estimada?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                <p class="text-sm text-zinc-200">Monto: USD {{ number_format($solicitud->monto_solicitado, 2) }}</p>
                                <p class="text-xs text-zinc-500">Desembolso: {{ $solicitud->monto_solicitado ? 'USD '.number_format($solicitud->monto_solicitado, 2) : 'Sin monto' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $badge }}">{{ ucfirst($solicitud->estado) }}</span>
                                @if($badgeAdmin)
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeAdmin }}">Auditoría: {{ ucfirst($solicitud->estado_admin) }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-zinc-300">{{ $solicitud->descripcion ?? 'Sin descripcion' }}</p>
                        @if($solicitud->justificacion_admin)
                            <p class="text-xs text-red-200">Nota auditoría: {{ $solicitud->justificacion_admin }}</p>
                        @endif
                        <div class="text-xs text-zinc-400">
                            <p class="font-semibold text-white">Proveedores:</p>
                            <p>{{ !empty($solicitud->proveedores) ? implode(', ', $solicitud->proveedores) : 'Sin proveedores especificados' }}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-zinc-900/60 px-3 py-2 text-xs text-zinc-300">
                            <p class="text-[11px] text-zinc-500">Adjuntos</p>
                            @if (!empty($solicitud->adjuntos))
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach ($solicitud->adjuntos as $idx => $archivo)
                                        <a href="{{ asset('storage/'.$archivo) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-1 hover:border-indigo-400/60">
                                            Archivo {{ $idx + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-white">Sin adjuntos</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-zinc-400">No hay solicitudes registradas para este proyecto.</p>
                @endforelse
            </div>
        </section>
    @endif
@endsection
