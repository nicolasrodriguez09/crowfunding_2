@extends('auditor.layouts.panel')

@section('title', 'Detalle de comprobante')
@section('active', 'comprobantes')
@section('back_url', route('auditor.comprobantes'))
@section('back_label', 'Volver a comprobantes')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

    @php
        $montoSolicitud = $pago->solicitud->monto_solicitado ?? 0;
        $porcentaje = $montoSolicitud > 0 ? round(($pago->monto / $montoSolicitud) * 100, 1) : 0;

        $aud = $pago->estado_auditoria ?? 'pendiente';
        $audBadge = 'border-purple-300/20 bg-purple-500/10 text-purple-200';
        if ($aud === 'rechazado') $audBadge = 'border-red-300/20 bg-red-500/10 text-red-200';
        if ($aud === 'aprobado')  $audBadge = 'border-emerald-300/20 bg-emerald-500/10 text-emerald-200';
    @endphp

    {{-- Header / contexto --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.10),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Comprobante</p>
                <h1 class="mt-2 text-2xl font-bold text-white truncate">
                    #{{ $pago->id }} — {{ $pago->concepto ?? 'Sin concepto' }}
                </h1>
                <p class="mt-1 text-sm text-zinc-400">
                    Fecha: {{ $pago->fecha_pago?->format('Y-m-d') ?? 'N/D' }} · Proveedor: {{ $pago->proveedor->nombre_proveedor ?? 'N/D' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border {{ $audBadge }} px-3 py-1 text-xs font-semibold">
                    Auditoría: {{ $aud }}
                </span>
                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-white">
                    Solicitud #{{ $pago->solicitud->id ?? 'N/D' }}
                </span>
            </div>
        </div>
    </section>

    {{-- Resumen en 3 cards --}}
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-5 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Monto del comprobante</p>
            <p class="text-3xl font-extrabold text-purple-200">${{ number_format($pago->monto, 2, ',', '.') }}</p>
            <p class="text-xs text-zinc-400">Concepto: {{ $pago->concepto ?? 'Sin concepto' }}</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-5 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Proyecto / hito</p>
            <p class="text-lg font-semibold text-white">{{ optional($pago->solicitud->proyecto)->titulo ?? 'Proyecto' }}</p>
            <p class="text-xs text-zinc-400">Hito del desembolso: {{ $pago->solicitud->hito ?? 'N/D' }}</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-5 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Solicitud de desembolso</p>
            <p class="text-lg font-semibold text-white">{{ $pago->solicitud->estado ?? 'N/D' }}</p>
            <p class="text-xs text-zinc-400">
                Monto: ${{ number_format($montoSolicitud, 2, ',', '.') }}
            </p>
            <p class="text-xs text-purple-200 font-semibold">Justificado: {{ $porcentaje }}%</p>
        </div>
    </section>

    {{-- Justificación (más sobria, morada) --}}
    <section class="rounded-3xl border border-purple-300/20 bg-purple-500/10 p-5 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-purple-200">Justificación vs desembolso</p>
                <p class="text-sm text-white/85">
                    Este comprobante cubre el <span class="font-semibold text-white">{{ $porcentaje }}%</span> del monto solicitado.
                </p>
            </div>
            <span class="inline-flex items-center rounded-full border border-purple-300/20 bg-white/5 px-4 py-2 text-sm font-semibold text-white">
                {{ $porcentaje }}% justificado
            </span>
        </div>

        <div class="h-2 rounded-full bg-white/10 overflow-hidden">
            <div class="h-2 rounded-full bg-purple-400" style="width: {{ min($porcentaje, 100) }}%;"></div>
        </div>

        <p class="text-xs text-zinc-300">
            Monto desembolso: ${{ number_format($montoSolicitud, 2, ',', '.') }}
            · Monto comprobante: ${{ number_format($pago->monto, 2, ',', '.') }}
        </p>
    </section>

    {{-- Detalles --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-3 shadow-xl">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Detalles</p>
        <p class="text-sm text-zinc-300">Concepto: {{ $pago->concepto ?? 'Sin concepto' }}</p>
        <p class="text-sm text-zinc-300">Proveedor contacto: {{ $pago->proveedor->info_contacto ?? 'N/D' }}</p>
    </section>

    {{-- Adjuntos --}}
    @if ($adjuntos->isNotEmpty())
        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-3 shadow-xl">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Adjuntos</p>
                <span class="text-xs text-zinc-400">{{ $adjuntos->count() }} archivo(s)</span>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($adjuntos as $file)
                    <a href="{{ $file['url'] }}" target="_blank"
                       class="group relative block overflow-hidden rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                        <img src="{{ $file['url'] }}" alt="Comprobante"
                             class="h-48 w-full object-cover transition duration-200 group-hover:scale-[1.02] group-hover:opacity-90"
                             onerror="this.style.display='none'">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                        <div class="absolute bottom-2 left-2 right-2 text-xs text-white/90 truncate">
                            {{ basename($file['path']) }}
                        </div>

                        <div class="absolute top-2 right-2 rounded-full border border-white/10 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                            Abrir
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Acciones de auditoría --}}
    <form action="{{ route('auditor.comprobantes.estado', $pago) }}" method="POST"
          class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-4 shadow-xl">
        @csrf
        @method('PATCH')

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Acciones de auditoría</p>
                <p class="text-sm text-zinc-400 mt-1">Aprobar o rechazar con evidencia. Rechazar requiere nota.</p>
            </div>
            <span class="inline-flex items-center rounded-full border {{ $audBadge }} px-3 py-1 text-xs font-semibold">
                Estado: {{ $aud }}
            </span>
        </div>

        <div>
            <label class="text-xs uppercase tracking-[0.2em] text-zinc-500">Nota (opcional, requerida para rechazar)</label>
            <textarea name="nota" rows="3"
                      class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white
                             placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none"
                      placeholder="Describe hallazgos o la razón de rechazo">{{ old('nota', $pago->nota_auditoria) }}</textarea>
        </div>

        <input type="hidden" name="accion" value="aprobar" id="accion-input">

        <div class="flex flex-wrap gap-2">
            <button type="submit"
                    onclick="document.getElementById('accion-input').value='aprobar'"
                    class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-5 py-2.5 text-xs font-semibold text-white
                           shadow-lg shadow-purple-900/30 hover:bg-purple-400">
                Aprobar
            </button>

            <button type="submit"
                    onclick="document.getElementById('accion-input').value='rechazar'"
                    class="inline-flex items-center justify-center rounded-2xl border border-red-300/30 bg-red-500/10 px-5 py-2.5 text-xs font-semibold text-red-200
                           hover:bg-red-500/15">
                Rechazar
            </button>
        </div>
    </form>

</div>
@endsection
