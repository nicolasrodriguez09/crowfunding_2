@extends('admin.layouts.panel')

@section('title', 'Proveedor')
@section('active', 'proveedores')
@section('back_url', route('admin.proveedores'))
@section('back_label', 'Volver a proveedores')

@section('content')
    <div class="space-y-8 px-4 sm:px-6 lg:px-8 pt-6">
        <section class="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Proveedor</p>
                    <h1 class="text-3xl font-bold text-white">{{ $proveedor->nombre_proveedor }}</h1>
                    <p class="text-sm text-zinc-300">Especialidad: {{ $proveedor->especialidad ?? 'Sin especialidad' }}</p>
                    <p class="text-xs text-zinc-500">Contacto: {{ $proveedor->info_contacto ?? 'N/D' }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-indigo-100">
                    @if ($proveedor->proyecto)
                        <a href="{{ route('admin.proyectos.show', $proveedor->proyecto) }}" class="admin-btn admin-btn-ghost">
                            Ver proyecto
                        </a>
                    @endif
                    @if ($proveedor->creador)
                        <a href="{{ route('admin.users.show', $proveedor->creador) }}" class="admin-btn admin-btn-ghost">
                            Ver creador
                        </a>
                    @endif
                </div>
            </div>

            @php
                $avg = $calificacionPromedio;
                $avgLabel = $avg ? number_format($avg, 1) . ' / 5' : 'Sin calificacion';
            @endphp
            <div class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Calificacion promedio</p>
                    <p class="text-2xl font-bold text-white">{{ $avgLabel }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Pagos registrados</p>
                    <p class="text-2xl font-bold text-white">{{ $pagoStats['count'] }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Monto total</p>
                    <p class="text-2xl font-bold text-white">${{ number_format($pagoStats['total'], 2, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Con evidencia</p>
                    <p class="text-2xl font-bold text-white">{{ $pagoStats['conAdjuntos'] }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2 text-xs text-zinc-300">
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/10 px-3 py-1 border border-amber-400/30 text-amber-100">
                    Proyecto: {{ $proveedor->proyecto->titulo ?? 'No asignado' }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 border border-emerald-400/30 text-emerald-100">
                    Creador: {{ $proveedor->creador->nombre_completo ?? $proveedor->creador->name ?? 'N/D' }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 border border-white/10 text-white/80">
                    Registrado: {{ $proveedor->created_at?->format('Y-m-d') ?? 'N/D' }}
                </span>
            </div>
        </section>

        <section class="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Pagos</p>
                    <h2 class="text-xl font-bold text-white">Gastos asociados al proveedor</h2>
                    <p class="text-sm text-zinc-400">Incluye estado de auditoria y si cuentan con evidencias.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-zinc-300">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 border border-white/15">
                        Pendientes: {{ $pagoStats['pendientes'] }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 border border-emerald-400/30 text-emerald-100">
                        Aprobados: {{ $pagoStats['aprobados'] }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-3 py-1 border border-rose-400/30 text-rose-100">
                        Observados/Rechazados: {{ $pagoStats['observados'] }}
                    </span>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="min-w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/5 text-sm">
                        <thead class="bg-white/5 text-left text-xs uppercase tracking-[0.2em] text-zinc-400">
                            <tr>
                                <th class="px-4 py-3">Proyecto</th>
                                <th class="px-4 py-3">Concepto</th>
                                <th class="px-4 py-3">Monto</th>
                                <th class="px-4 py-3">Fecha pago</th>
                                <th class="px-4 py-3">Auditoria</th>
                                <th class="px-4 py-3">Evidencias</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($pagos as $pago)
                                @php
                                    $estado = ucfirst($pago->estado_auditoria ?? 'pendiente');
                                    $estadoClass = match ($pago->estado_auditoria) {
                                        'aprobado' => 'bg-emerald-500/15 text-emerald-100 border-emerald-400/40',
                                        'rechazado', 'observado' => 'bg-rose-500/15 text-rose-100 border-rose-400/40',
                                        default => 'bg-white/10 text-zinc-200 border-white/15',
                                    };
                                    $adjuntos = is_array($pago->adjuntos) ? count($pago->adjuntos) : 0;
                                @endphp
                                <tr class="text-zinc-200">
                                    <td class="px-4 py-3">
                                        {{ $pago->solicitud->proyecto->titulo ?? 'Proyecto' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $pago->concepto ?? 'Sin concepto' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold">
                                        ${{ number_format($pago->monto, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $pago->fecha_pago?->format('Y-m-d') ?? 'N/D' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold {{ $estadoClass }}">
                                            {{ $estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $adjuntos }} archivo(s)
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-sm text-zinc-400">No hay pagos registrados para este proveedor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Historial</p>
                <h2 class="text-xl font-bold text-white">Compras y calificaciones</h2>
                <p class="text-sm text-zinc-400">Registros cargados por el creador.</p>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($proveedor->historiales as $item)
                    @php
                        $calificacion = $item->calificacion ? $item->calificacion . ' / 5' : null;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $item->concepto }}</p>
                                <p class="text-xs text-zinc-400">Entrega: {{ $item->fecha_entrega?->format('Y-m-d') ?? 'Sin fecha' }}</p>
                                <p class="text-xs text-zinc-500">Registrado: {{ $item->created_at?->format('Y-m-d H:i') }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-emerald-100 border border-emerald-400/30">
                                    ${{ number_format($item->monto, 2, ',', '.') }}
                                </span>
                                @if ($calificacion)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-3 py-1 text-indigo-100 border border-indigo-400/30">
                                        Calificacion: {{ $calificacion }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-zinc-400">No hay historial registrado para este proveedor.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
