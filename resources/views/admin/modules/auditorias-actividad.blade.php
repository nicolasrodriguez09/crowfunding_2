@extends('admin.layouts.panel')

@section('title', 'Actividad de auditoría')
@section('active', 'auditorias')
@section('back_url', route('admin.auditorias'))
@section('back_label', 'Volver a auditorías')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-white/10 bg-zinc-900/75 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="border-b border-white/5 px-6 py-6 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-zinc-400">Actividad reciente</p>
                <h2 class="text-2xl font-bold text-white">Timeline de auditoría</h2>
                <p class="text-sm text-zinc-400">Filtra por fecha o texto para revisar movimientos de reportes y pagos auditados.</p>
            </div>

            <form method="GET" action="{{ route('admin.auditorias.actividad') }}" class="grid gap-3 md:grid-cols-[2fr,1fr,1fr,auto] md:items-end px-6 py-5">
                <div>
                    <label class="text-xs text-zinc-400">Búsqueda</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Proyecto, proveedor, motivo..."
                           class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-zinc-500 focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="mt-1 w-full rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-white/40 focus:ring-white/20">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="mt-1 w-full rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-white/40 focus:ring-white/20">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#4f46e5] px-4 py-2.5 text-sm font-semibold text-white border border-[#4f46e5] hover:bg-[#4338ca]">
                        Filtrar
                    </button>
                    <a href="{{ route('admin.auditorias.actividad') }}" class="admin-btn admin-btn-ghost">
                        Limpiar
                    </a>
                </div>
            </form>

            <div class="px-6 pb-6">
                @if($eventos->isEmpty())
                    <p class="text-sm text-zinc-400">No hay actividad registrada en el rango seleccionado.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($eventos as $evento)
                            @php
                                $isPago = ($evento['tipo'] ?? '') === 'pago';
                                $pillClass = $isPago
                                    ? 'bg-emerald-500/10 text-emerald-100 border border-emerald-400/40'
                                    : 'bg-amber-500/10 text-amber-100 border border-amber-400/40';
                                $label = $isPago ? 'Pago' : 'Reporte';
                            @endphp
                            <article class="rounded-2xl border border-white/10 bg-white/5 p-4 flex flex-col gap-2">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold {{ $pillClass }}">
                                        {{ $label }}
                                    </span>
                                    <span class="text-xs text-zinc-400">{{ $evento['timestamp'] ?? '' }}</span>
                                </div>
                                <p class="text-sm text-white">{{ $evento['mensaje'] ?? '' }}</p>
                                @if (!empty($evento['link']))
                                    <div>
                                        <a href="{{ $evento['link'] }}" class="text-[12px] font-semibold text-indigo-200 hover:text-white inline-flex items-center gap-1">
                                            Ver detalle
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
