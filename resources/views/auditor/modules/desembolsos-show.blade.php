@extends('auditor.layouts.panel')

@section('title', 'Detalle de desembolso')
@section('active', 'desembolsos')
@section('back_url', route('auditor.desembolsos'))
@section('back_label', 'Volver a desembolsos')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

    @php
        $st = strtolower($solicitud->estado ?? 'pendiente');

        $badge = 'border-purple-300/20 bg-purple-500/10 text-purple-200';
        if (str_contains($st, 'rech')) $badge = 'border-red-300/20 bg-red-500/10 text-red-200';
        if (str_contains($st, 'aprob') || str_contains($st, 'liber')) $badge = 'border-emerald-300/20 bg-emerald-500/10 text-emerald-200';
    @endphp

    {{-- HERO / HEADER (mismo estilo del auditor) --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10
                    bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
                    px-8 py-7 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Solicitud de desembolso</p>
                <h1 class="mt-2 text-2xl sm:text-3xl font-black text-white truncate">
                    #{{ $solicitud->id }} — {{ $solicitud->hito ?? 'Hito' }}
                </h1>
                <p class="mt-1 text-sm text-white/80 truncate">
                    Proyecto: {{ $solicitud->proyecto->titulo ?? 'Proyecto' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border {{ $badge }} px-3 py-1 text-xs font-semibold">
                    Estado: {{ ucfirst($solicitud->estado ?? 'pendiente') }}
                </span>

                @if($solicitud->created_at)
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold text-white/90 backdrop-blur">
                        Creada: {{ $solicitud->created_at->format('Y-m-d H:i') }}
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- RESUMEN (3 cards) --}}
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Monto solicitado</p>
            <p class="text-3xl font-extrabold text-purple-200">
                ${{ number_format($solicitud->monto_solicitado, 0, ',', '.') }}
            </p>
            <p class="text-xs text-zinc-400">
                Fecha estimada: {{ $solicitud->fecha_estimada?->format('Y-m-d') ?? 'N/D' }}
            </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Estado</p>
            <p class="text-lg font-semibold text-white">{{ ucfirst($solicitud->estado ?? 'N/D') }}</p>
            <p class="text-xs text-zinc-400">Proyecto ID: {{ $solicitud->proyecto_id ?? 'N/D' }}</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-2 shadow-xl">
            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Proveedores</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($solicitud->proveedores ?? [] as $prov)
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white">
                        {{ $prov }}
                    </span>
                @empty
                    <p class="text-xs text-zinc-400">N/D</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Descripción --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-3 shadow-xl">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Descripción</p>
        <p class="text-sm text-zinc-300 leading-relaxed">
            {{ $solicitud->descripcion ?? 'Sin descripción' }}
        </p>
    </section>

    {{-- Adjuntos (mejorado con overlay + badge "Abrir") --}}
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
                        <img src="{{ $file['url'] }}" alt="Adjunto"
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

    {{-- Acciones (mismo patrón que comprobantes.show) --}}
    <form action="{{ route('auditor.desembolsos.estado', $solicitud) }}" method="POST"
          class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 space-y-4 shadow-xl">
        @csrf
        @method('PATCH')

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Acciones de auditoría</p>
                <p class="text-sm text-zinc-400 mt-1">Aprobar o rechazar la solicitud. Rechazar requiere nota.</p>
            </div>
            <span class="inline-flex items-center rounded-full border {{ $badge }} px-3 py-1 text-xs font-semibold">
                Estado: {{ ucfirst($solicitud->estado ?? 'pendiente') }}
            </span>
        </div>

        <div>
            <label class="text-xs uppercase tracking-[0.2em] text-zinc-500">Nota (requerida para rechazar)</label>
            <textarea name="nota" rows="3"
                      class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white
                             placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none"
                      placeholder="Describe hallazgos o razón de rechazo">{{ old('nota', $solicitud->justificacion_admin) }}</textarea>
        </div>

        <input type="hidden" name="accion" value="aprobar" id="accion-desembolso">

        <div class="flex flex-wrap gap-2">
            <button type="submit"
                    onclick="document.getElementById('accion-desembolso').value='aprobar'"
                    class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-5 py-2.5 text-xs font-semibold text-white
                           shadow-lg shadow-purple-900/30 hover:bg-purple-400">
                Aprobar
            </button>

            <button type="submit"
                    onclick="document.getElementById('accion-desembolso').value='rechazar'"
                    class="inline-flex items-center justify-center rounded-2xl border border-red-300/30 bg-red-500/10 px-5 py-2.5 text-xs font-semibold text-red-200
                           hover:bg-red-500/15">
                Rechazar
            </button>
        </div>

        <p class="text-xs text-zinc-500">Estado actual: {{ $solicitud->estado ?? 'N/D' }}</p>
    </form>

</div>
@endsection
