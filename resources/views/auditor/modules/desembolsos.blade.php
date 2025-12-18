@extends('auditor.layouts.panel')

@section('title', 'Desembolsos en revisión')
@section('active', 'desembolsos')

@section('content')
<div class="space-y-10 px-4 sm:px-6 lg:px-8">

    {{-- HERO / HEADER (estilo auditor morado) --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10
                    bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
                    px-8 py-8 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Módulo 3</p>
                <h1 class="mt-3 text-3xl font-black text-white">Solicitudes de desembolso</h1>
                <p class="mt-2 text-sm text-white/80">
                    Revisa hitos, evidencia y estado del proyecto antes de liberar fondos. Prioriza lo pendiente.
                </p>
            </div>

            {{-- Pills (estado rápido) --}}
            <div class="grid gap-3 text-sm text-white sm:grid-cols-2">
                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                        Cola de revisión
                    </span>
                    <span class="font-semibold">
                        {{ $solicitudes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $solicitudes->total() : (is_countable($solicitudes) ? count($solicitudes) : 0) }}
                    </span>
                </div>
                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Filtros activos
                    </span>
                    <span class="font-semibold">{{ ($q ?? '') !== '' || ($estado ?? '') !== '' ? 'Sí' : 'No' }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- FILTROS (card) --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Búsqueda</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Filtrar solicitudes</h2>
                <p class="mt-1 text-sm text-zinc-400">Busca por proyecto y filtra por estado.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-purple-300/20 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-200">
                    {{ $solicitudes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $solicitudes->count() : (is_countable($solicitudes) ? count($solicitudes) : 0) }}
                    en vista
                </span>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr_240px_auto] items-end">
            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Buscar</label>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Proyecto"
                       class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white
                              placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Estado</label>
                <select name="estado"
                        class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white
                               focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none">
                    <option value="">Todos</option>
                    @foreach ($estados as $opt)
                        <option value="{{ $opt }}" {{ ($estado ?? '') === $opt ? 'selected' : '' }}>
                            {{ ucfirst($opt) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-5 py-2.5 text-xs font-semibold text-white
                               shadow-lg shadow-purple-900/40 hover:bg-purple-400">
                    Filtrar
                </button>
                <a href="{{ route('auditor.desembolsos') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white
                          hover:bg-white/10">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- LISTADO / COLA DE REVISIÓN --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Desembolsos</p>
                <h3 class="mt-1 text-lg font-semibold text-white">Solicitudes en revisión</h3>
                <p class="mt-1 text-sm text-zinc-400">Abre el detalle para revisar hitos, soporte y liberar fondos.</p>
            </div>
        </div>

        {{-- Encabezado (escritorio) --}}
        <div class="hidden md:grid md:grid-cols-12 gap-4 px-6 py-3 text-[11px] uppercase tracking-[0.22em] text-zinc-400 font-semibold bg-white/3">
            <div class="md:col-span-2">Solicitud</div>
            <div class="md:col-span-4">Proyecto / Hito</div>
            <div class="md:col-span-2">Monto</div>
            <div class="md:col-span-3">Fecha / Estado</div>
            <div class="md:col-span-1 text-right">Acción</div>
        </div>

        <div class="px-6 py-4 space-y-3">
            @forelse ($solicitudes as $item)
                @php
                    $st = strtolower($item->estado ?? 'pendiente');

                    $badge = 'border-purple-300/20 bg-purple-500/10 text-purple-200';
                    if (str_contains($st, 'rech')) $badge = 'border-red-300/20 bg-red-500/10 text-red-200';
                    if (str_contains($st, 'aprob') || str_contains($st, 'liber')) $badge = 'border-emerald-300/20 bg-emerald-500/10 text-emerald-200';
                @endphp

                <div class="rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-4 items-start md:items-center">
                        {{-- Solicitud --}}
                        <div class="md:col-span-2">
                            <p class="font-semibold text-white">#{{ $item->id }}</p>
                            <div class="mt-2 inline-flex items-center rounded-full border {{ $badge }} px-2.5 py-1 text-[11px] font-semibold">
                                {{ $item->estado ?? 'pendiente' }}
                            </div>
                        </div>

                        {{-- Proyecto / Hito --}}
                        <div class="md:col-span-4">
                            <p class="font-semibold text-white">
                                {{ $item->proyecto->titulo ?? 'Proyecto' }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                Hito: {{ $item->hito ?? 'Hito' }}
                            </p>
                        </div>

                        {{-- Monto --}}
                        <div class="md:col-span-2">
                            <p class="text-lg font-semibold text-purple-200">
                                ${{ number_format($item->monto_solicitado, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-zinc-500">Monto solicitado</p>
                        </div>

                        {{-- Fecha / Estado --}}
                        <div class="md:col-span-3">
                            <p class="text-sm text-white">
                                {{ $item->created_at?->format('Y-m-d') ?? 'N/D' }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                Estado: {{ $item->estado ?? 'N/D' }}
                            </p>
                        </div>

                        {{-- Acción --}}
                        <div class="md:col-span-1 md:text-right">
                            <a href="{{ route('auditor.desembolsos.show', $item) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-purple-500 px-3 py-2 text-xs font-semibold text-white
                                      shadow-lg shadow-purple-900/30 hover:bg-purple-400 w-full md:w-auto">
                                Ver detalle
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-white/10 bg-white/5 px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-white">No hay solicitudes registradas</p>
                    <p class="mt-1 text-xs text-zinc-400">Cuando existan, aparecerán aquí para revisión.</p>
                    <a href="{{ route('auditor.desembolsos') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white hover:bg-white/10">
                        Recargar
                    </a>
                </div>
            @endforelse
        </div>

        <div class="px-6 py-5 border-t border-white/10">
            {{ $solicitudes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $solicitudes->links() : '' }}
        </div>
    </section>

</div>
@endsection
