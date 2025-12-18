@extends('creator.layouts.panel')

@section('title', 'Detalle de desembolso')
@section('active', 'fondos')
@section('back_url', route('creador.fondos', ['proyecto' => $proyecto->id ?? null]))
@section('back_label', 'Volver a fondos')

@section('content')
<div class="space-y-6 px-4 sm:px-6 lg:px-8">
    <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-emerald-600/25 via-zinc-900/70 to-zinc-900/70 p-8 shadow-2xl ring-1 ring-indigo-500/10 space-y-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Fondos y desembolsos</p>
                <h1 class="text-2xl font-bold text-white">Detalle de la solicitud</h1>
                <p class="text-sm text-zinc-300">Revisa el estado, adjuntos y comentarios de auditoría para este desembolso.</p>
            </div>
            <div class="text-right text-xs text-zinc-300 space-y-1">
                <p class="font-semibold text-white">{{ $proyecto->titulo ?? 'Proyecto' }}</p>
                <p class="text-zinc-400">Registrado: {{ $solicitud->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
            </div>
        </div>
        @php
            $estadoBadges = [
                'pendiente' => 'bg-amber-500/15 text-amber-100 border border-amber-400/30',
                'aprobado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                'liberado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                'pagado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                'rechazado' => 'bg-red-500/15 text-red-100 border border-red-400/30',
                'gastado' => 'bg-sky-500/15 text-sky-100 border border-sky-400/30',
                'observado' => 'bg-amber-500/15 text-amber-100 border border-amber-400/30',
            ];
            $estado = $solicitud->estado ?? 'pendiente';
            $estadoAdmin = $solicitud->estado_admin ?? null;
        @endphp
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner space-y-1">
                <p class="text-[11px] text-zinc-400 uppercase tracking-[0.25em]">Estado solicitud</p>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-semibold text-white">{{ ucfirst($estado) }}</span>
                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $estadoBadges[$estado] ?? 'bg-white/10 text-white border border-white/20' }}">{{ ucfirst($estado) }}</span>
                </div>
                <p class="text-xs text-zinc-400">Flujo de liberación de fondos.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner space-y-1">
                <p class="text-[11px] text-zinc-400 uppercase tracking-[0.25em]">Auditoría</p>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-semibold text-white">{{ $estadoAdmin ? ucfirst($estadoAdmin) : 'Pendiente' }}</span>
                    @if($estadoAdmin)
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $estadoBadges[$estadoAdmin] ?? 'bg-white/10 text-white border border-white/20' }}">Auditoría</span>
                    @else
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold bg-amber-500/15 text-amber-100 border border-amber-400/30">Sin dictamen</span>
                    @endif
                </div>
                <p class="text-xs text-zinc-400">Estado según revisión del auditor.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner space-y-1">
                <p class="text-[11px] text-zinc-400 uppercase tracking-[0.25em]">Monto solicitado</p>
                <p class="text-2xl font-bold text-emerald-200">USD {{ number_format($solicitud->monto_solicitado, 2) }}</p>
                <p class="text-xs text-zinc-400">Fecha estimada: {{ $solicitud->fecha_estimada?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-indigo-500/10 space-y-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Resumen</p>
                <h2 class="text-lg font-bold text-white">Ficha de la solicitud</h2>
                <p class="text-sm text-zinc-400">Incluye hito, descripción, proveedores y adjuntos.</p>
            </div>
            <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-zinc-200">ID solicitud #{{ $solicitud->id }}</span>
        </div>

        <div class="grid gap-4 md:grid-cols-[1.1fr,0.9fr]">
            <article class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-inner space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $solicitud->hito ?? 'Hito financiero' }}</p>
                        <p class="text-xs text-zinc-400">
                            Registrado: {{ $solicitud->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                            · Estimada: {{ $solicitud->fecha_estimada?->format('d/m/Y') ?? 'Sin fecha' }}
                        </p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $estadoBadges[$estado] ?? 'bg-white/10 text-white border border-white/20' }}">{{ ucfirst($estado) }}</span>
                </div>
                <p class="text-sm text-zinc-300">Monto solicitado: <span class="font-semibold text-emerald-200">USD {{ number_format($solicitud->monto_solicitado, 2) }}</span></p>
                <p class="text-sm text-zinc-300">Descripción: {{ $solicitud->descripcion ?? 'Sin descripción' }}</p>
                <div class="rounded-xl border border-white/10 bg-zinc-900/60 px-4 py-3 text-sm text-zinc-200 space-y-1">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-zinc-400">Proveedores</p>
                    <p>{{ !empty($solicitud->proveedores) ? implode(', ', $solicitud->proveedores) : 'Sin proveedores especificados' }}</p>
                </div>
                @if ($solicitud->justificacion_admin)
                    <div class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-50">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-red-200">Nota de auditoría</p>
                        <p>{{ $solicitud->justificacion_admin }}</p>
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-inner space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Adjuntos</p>
                        <h3 class="text-sm font-semibold text-white">Evidencia cargada</h3>
                        <p class="text-xs text-zinc-400">Cotizaciones, contratos o soporte.</p>
                    </div>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold text-zinc-200">{{ $adjuntos->count() }} archivo(s)</span>
                </div>
                @if ($adjuntos->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($adjuntos as $idx => $archivo)
                            <a href="{{ $archivo['url'] }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-zinc-900/70 px-3 py-2 text-xs text-white hover:border-emerald-400/60">
                                Archivo {{ $idx + 1 }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-400">Sin adjuntos registrados para esta solicitud.</p>
                @endif
            </article>
        </div>
    </section>
</div>
@endsection
