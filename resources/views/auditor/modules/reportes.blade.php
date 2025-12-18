@extends('auditor.layouts.panel')

@section('title', 'Reportes sospechosos')
@section('active', 'sospechosos')

@push('head')
<style>
    /* Uniform dark theme for the estado dropdown */
    [data-estado-select] {
        background-color: #111827;
        color: #fff;
    }

    [data-estado-select] option {
        background-color: #111827;
        color: #fff;
    }

    [data-estado-select] option:checked,
    [data-estado-select] option:hover {
        background-color: #1f2937;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="space-y-10 px-4 sm:px-6 lg:px-8">

    {{-- HERO / HEADER (mismo patrón auditor) --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10
                    bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
                    px-8 py-8 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Módulo 4</p>
                <h1 class="mt-3 text-3xl font-black text-white">Reportes sospechosos</h1>
                <p class="mt-2 text-sm text-white/80">
                    Centraliza denuncias, revisa evidencia y deja trazabilidad con tu decisión.
                </p>
            </div>

            {{-- Pills (estado rápido) --}}
            <div class="grid gap-3 text-sm text-white sm:grid-cols-2">
                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                        Cola de casos
                    </span>
                    <span class="font-semibold">
                        {{ $reportesColab instanceof \Illuminate\Pagination\LengthAwarePaginator ? $reportesColab->total() : (is_countable($reportesColab) ? count($reportesColab) : 0) }}
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

    {{-- FILTROS (card sobria) --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Búsqueda</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Filtrar reportes</h2>
                <p class="mt-1 text-sm text-zinc-400">Busca por proyecto, colaborador o motivo. Filtra por estado.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('auditor.reportes') }}" class="mt-5 grid gap-3 md:grid-cols-[1.5fr_260px_auto] md:items-end">
            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Buscar</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Proyecto, colaborador o parte del motivo"
                    class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white
                           placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none"
                >
                <p class="text-xs text-zinc-500 mt-2">La búsqueda recorre títulos, nombres y descripciones.</p>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Estado</label>
                <div class="relative mt-1">
                    <select
                        name="estado"
                        data-estado-select
                        class="w-full appearance-none rounded-2xl border border-white/15 bg-[#111827] px-4 py-2.5 pr-10 text-sm text-white
                               focus:border-purple-300 focus:ring-2 focus:ring-purple-500/25 focus:outline-none"
                        style="background-color:#111827;color:#fff;"
                    >
                        @php
                            $selectOptionStyle = 'background-color:#111827;color:#fff;';
                            $estadosFiltro = collect(['pendiente', 'aprobado', 'rechazado'])
                                ->merge($estados ?? collect())
                                ->unique()
                                ->values();
                        @endphp
                        <option value="" style="{{ $selectOptionStyle }}">Todos</option>
                        @foreach ($estadosFiltro as $opt)
                            <option value="{{ $opt }}" {{ ($estado ?? '') === $opt ? 'selected' : '' }} style="{{ $selectOptionStyle }}">
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-5 py-2.5 text-xs font-semibold text-white
                               shadow-lg shadow-purple-900/40 hover:bg-purple-400 w-full">
                    Filtrar
                </button>
                <a href="{{ route('auditor.reportes') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white
                          hover:bg-white/10 w-full">
                    Limpiar
                </a>
            </div>
        </form>

        @if(session('status'))
            <div class="mt-5 rounded-2xl border border-emerald-300/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif
    </section>

    {{-- LISTA DE REPORTES --}}
    <section class="space-y-4">
        @forelse ($reportesColab as $item)
            @php
                $estadoActual = $item->estado ?? 'pendiente';

                $estadoClase = match ($estadoActual) {
                    'aprobado' => 'bg-emerald-500/10 text-emerald-200 border-emerald-300/20',
                    'rechazado' => 'bg-red-500/10 text-red-200 border-red-300/20',
                    default => 'bg-purple-500/10 text-purple-200 border-purple-300/20',
                };
            @endphp

            <article class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">
                            Caso #{{ $item->id }} · {{ $item->proyecto->titulo ?? 'Proyecto' }}
                        </p>
                        <p class="mt-2 text-sm text-zinc-400">
                            Colaborador: {{ $item->colaborador->nombre_completo ?? $item->colaborador->name ?? 'N/D' }}
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-1 text-right">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold {{ $estadoClase }}">
                            <span class="h-2 w-2 rounded-full bg-current opacity-80"></span>
                            {{ ucfirst($estadoActual) }}
                        </span>
                        <p class="text-[11px] text-zinc-500">
                            Enviado {{ optional($item->created_at)->format('d/m/Y H:i') ?? 'N/D' }}
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-base font-semibold text-white leading-relaxed">
                    {{ $item->motivo }}
                </p>

                @if (!empty($item->evidencias))
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($item->evidencias as $idx => $path)
                            <a href="{{ asset('storage/'.$path) }}" target="_blank"
                               class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-white
                                      hover:border-purple-300/30 hover:bg-white/10">
                                <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                                Evidencia {{ $idx + 1 }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('auditor.reportes.estado', $item) }}"
                      class="mt-5 space-y-3 border-t border-white/10 pt-4" data-auditor-report>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reporte_id" value="{{ $item->id }}">

                    <div>
                        <label class="text-xs uppercase tracking-[0.3em] text-zinc-500">Explicación del auditor</label>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Queda registrada en el historial. Obligatorio (mín. 20 caracteres, máx. 500).
                        </p>

                        <textarea
                            name="respuesta"
                            rows="4"
                            class="mt-3 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white
                                   placeholder:text-zinc-500 focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 focus:outline-none"
                            placeholder="Describe por qué apruebas o rechazas este reporte."
                            maxlength="500"
                        >{{ old('reporte_id') == $item->id ? old('respuesta') : ($item->respuesta ?? '') }}</textarea>

                        @if ($errors->has('respuesta') && old('reporte_id') == $item->id)
                            <p class="mt-2 text-xs text-red-300">{{ $errors->first('respuesta') }}</p>
                        @endif

                        <div class="mt-2 flex justify-between text-[11px] text-zinc-500">
                            <span>Mínimo 20 caracteres requeridos.</span>
                            <span data-char-count class="font-mono">0 / 500</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" name="accion" value="rechazar"
                                class="inline-flex items-center justify-center rounded-2xl border border-red-300/30 bg-red-500/10 px-4 py-2.5 text-xs font-semibold text-red-200
                                       hover:bg-red-500/15 flex-1 opacity-60" disabled>
                            Rechazar
                        </button>

                        <button type="submit" name="accion" value="aprobar"
                                class="inline-flex items-center justify-center rounded-2xl bg-purple-500 px-4 py-2.5 text-xs font-semibold text-white
                                       shadow-lg shadow-purple-900/30 hover:bg-purple-400 flex-1 opacity-60" disabled>
                            Marcar como aprobado
                        </button>
                    </div>
                </form>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-white/10 bg-zinc-900/70 px-6 py-10 text-center shadow-xl">
                <p class="text-sm font-semibold text-white">No hay reportes registrados</p>
                <p class="mt-1 text-xs text-zinc-400">Cuando existan, aparecerán aquí para revisión.</p>
            </div>
        @endforelse
    </section>

    @if ($reportesColab instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $reportesColab->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const minChars = 20;
    document.querySelectorAll('[data-auditor-report]').forEach(form => {
        const textarea = form.querySelector('textarea[name="respuesta"]');
        const counter = form.querySelector('[data-char-count]');
        const buttons = Array.from(form.querySelectorAll('button[name="accion"]'));

        const updateState = () => {
            const length = textarea?.value.trim().length ?? 0;
            if (counter) counter.textContent = `${Math.min(length, 500)} / 500`;
            buttons.forEach(btn => {
                const meets = length >= minChars;
                btn.disabled = !meets;
                btn.classList.toggle('opacity-60', !meets);
            });
        };

        textarea?.addEventListener('input', updateState);
        updateState();
    });
});
</script>
@endpush
