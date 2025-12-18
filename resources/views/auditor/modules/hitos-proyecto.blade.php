@extends('auditor.layouts.panel')

@section('title', 'Hitos de proyecto')
@section('active', 'hitos')
@section('back_url', route('auditor.hitos'))
@section('back_label', 'Volver a proyectos')

@section('content')
<div class="space-y-10 px-4 sm:px-6 lg:px-8 pt-6">

    {{-- HERO / HEADER (mismo estilo auditor) --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10
                    bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
                    px-8 py-8 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Proyecto</p>
                <h1 class="mt-3 text-3xl font-black text-white truncate">{{ $proyecto->titulo }}</h1>
                <p class="mt-2 text-sm text-white/80">Hitos y evidencias asociadas al proyecto.</p>
            </div>

            {{-- Pills / resumen rápido --}}
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm text-white backdrop-blur">
                    <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                    Hitos: <span class="font-semibold">{{ $hitos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $hitos->total() : (is_countable($hitos) ? count($hitos) : 0) }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm text-white backdrop-blur">
                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                    Filtro: <span class="font-semibold">{{ ($q ?? '') !== '' ? 'Activo' : 'No' }}</span>
                </span>
            </div>
        </div>
    </section>

    {{-- FILTRO (card sobria) --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Búsqueda</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Filtrar hitos</h2>
                <p class="mt-1 text-sm text-zinc-400">Busca por título del hito o contenido.</p>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr_auto] items-end">
            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Buscar</label>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Hito o contenido"
                       class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white
                              placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-5 py-2.5 text-xs font-semibold text-white
                               shadow-lg shadow-purple-900/40 hover:bg-purple-400">
                    Filtrar
                </button>
                <a href="{{ route('auditor.hitos.proyecto', $proyecto) }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white
                          hover:bg-white/10">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- LISTADO tipo “timeline” --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Hitos</p>
                <h3 class="mt-1 text-lg font-semibold text-white">Registro de avances</h3>
                <p class="mt-1 text-sm text-zinc-400">Entradas publicadas por el creador con evidencia y contexto.</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($hitos as $item)
                <article class="relative rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10 transition">
                    {{-- “marca” visual de timeline --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-purple-500/50 rounded-l-3xl"></div>

                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">
                                {{ $item->fecha_publicacion?->format('Y-m-d') ?? 'Sin fecha' }}
                            </p>
                            <h3 class="mt-2 text-xl font-semibold text-white truncate">{{ $item->titulo }}</h3>
                        </div>

                        <span class="inline-flex items-center rounded-full border border-purple-300/20 bg-purple-500/10 px-3 py-1 text-[11px] font-semibold text-purple-200">
                            ID {{ $item->id }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-zinc-300 leading-relaxed">
                        {{ $item->contenido ?? 'Sin contenido' }}
                    </p>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-white/10 bg-white/5 px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-white">No hay hitos para este proyecto</p>
                    <p class="mt-1 text-xs text-zinc-400">Cuando el creador publique avances, aparecerán aquí.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $hitos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $hitos->links() : '' }}
        </div>
    </section>

</div>
@endsection
