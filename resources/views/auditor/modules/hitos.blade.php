@extends('auditor.layouts.panel')

@section('title', 'Validacion de hitos')
@section('active', 'hitos')

@push('head')
<style>
    /* Dropdown oscuro para filtros */
    [data-select-dark] {
        background-color: #0f172a;
        color: #fff;
    }
    [data-select-dark] option {
        background-color: #0f172a;
        color: #fff;
    }
    [data-select-dark] option:checked,
    [data-select-dark] option:hover {
        background-color: #1f2937;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="space-y-8 px-4 sm:px-6 lg:px-8 pt-6">
    <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-r from-indigo-900/80 via-zinc-900/80 to-slate-900/80 px-6 py-7 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.12),_transparent_45%)]"></div>
        <div class="relative flex flex-col gap-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-white/70">Modulo 5</p>
            <h1 class="text-3xl font-black text-white">Validación de hitos</h1>
            <p class="text-sm text-zinc-200/80">Filtra por categoría o nombre y revisa el avance de cada proyecto.</p>
        </div>
    </section>

    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-5 shadow-xl">
        <form method="GET" class="grid gap-4 lg:grid-cols-[1.3fr_260px_auto] lg:items-end">
            <div class="space-y-2">
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Buscar proyecto</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Nombre del proyecto"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-zinc-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
            </div>
            <div class="space-y-2">
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Categoría</label>
                <select
                    name="categoria"
                    data-select-dark
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                    <option value="">Todas</option>
                    @foreach ($categorias ?? [] as $cat)
                        <option value="{{ $cat }}" @selected(($categoria ?? '') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-500 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-indigo-900/40 hover:bg-indigo-400 w-full">
                    Filtrar
                </button>
                <a href="{{ route('auditor.hitos') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white hover:bg-white/10 w-full">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-5">
        @forelse ($proyectos as $proy)
            @php
                $progress = $proy->progreso_hitos ?? 0;
                $planificados = $proy->cronograma_planificados ?? 0;
                $reportados = $proy->hitos_reportados ?? 0;
                $portada = $proy->portada_url ?? null;
                $fallbackGradient = 'linear-gradient(135deg,#111827 0%,#1f2937 50%,#0f172a 100%)';
            @endphp
            <article class="rounded-3xl border border-white/10 bg-zinc-900/70 p-5 shadow-xl ring-1 ring-indigo-500/10">
                <div class="grid gap-4 md:grid-cols-[240px_1fr_auto] md:items-center">
                    <div class="relative">
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-white/10 bg-zinc-900/60 shadow-inner">
                            <div class="absolute inset-0" style="background-image: {{ $portada ? "linear-gradient(135deg,rgba(0,0,0,0.45),rgba(0,0,0,0.25)), url('{$portada}')" : $fallbackGradient }}; background-size: cover; background-position: center;"></div>
                            <div class="relative flex h-full flex-col justify-between p-3 text-xs text-white">
                                <div class="inline-flex items-center gap-2 rounded-full bg-black/40 px-3 py-1 border border-white/10 backdrop-blur">
                                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                    {{ $proy->categoria ?: 'Sin categoría' }}
                                </div>
                                @unless($portada)
                                    <span class="inline-flex w-max items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-[11px] text-zinc-200">Sin portada</span>
                                @endunless
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Proyecto</p>
                            <span class="text-[11px] rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-zinc-200">Hitos reportados: {{ $reportados }}</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white">{{ $proy->titulo }}</h3>
                        <p class="text-sm text-zinc-400 line-clamp-2">{{ $proy->descripcion_proyecto ?? 'Sin descripción' }}</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[11px] text-zinc-400">Recaudado</p>
                                <p class="text-sm font-semibold text-white">${{ number_format($proy->monto_recaudado ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[11px] text-zinc-400">Meta</p>
                                <p class="text-sm font-semibold text-white">${{ number_format($proy->meta_financiacion ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[11px] text-zinc-400">Ubicación</p>
                                <p class="text-sm font-semibold text-white">{{ $proy->ubicacion_geografica ?? 'No definida' }}</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-zinc-400">
                                <span>Progreso de hitos</span>
                                <span class="text-zinc-200 font-semibold">{{ $progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-white/5">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 via-indigo-400 to-purple-400 shadow-[0_0_12px_rgba(79,70,229,0.4)]" style="width: {{ $progress }}%;"></div>
                            </div>
                            <p class="text-[11px] text-zinc-400">
                                {{ $reportados }} {{ \Illuminate\Support\Str::plural('hito', $reportados) }}
                                @if($planificados)
                                    de {{ $planificados }} planificados
                                @else
                                    reportados (sin cronograma cargado)
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch gap-3 justify-center">
                        <a href="{{ route('auditor.hitos.proyecto', $proy) }}" class="inline-flex items-center justify-center rounded-2xl bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-900/40 hover:bg-indigo-400">
                            Ver hitos
                        </a>
                        <span class="text-[11px] text-zinc-500 text-center">ID {{ $proy->id }}</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-white/10 bg-zinc-900/70 px-6 py-10 text-center shadow-xl">
                <p class="text-sm font-semibold text-white">No hay proyectos con hitos.</p>
                <p class="mt-1 text-xs text-zinc-400">Aparecerán aquí cuando publiquen hitos.</p>
            </div>
        @endforelse
    </section>

    <div>
        {{ $proyectos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $proyectos->links() : '' }}
    </div>
</div>
@endsection
