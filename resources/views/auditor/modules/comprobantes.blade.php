@extends('auditor.layouts.panel')

@section('title', 'Comprobantes de gasto')
@section('active', 'comprobantes')

@section('content')
<div class="space-y-10 px-4 sm:px-6 lg:px-8">

    {{-- HERO / HEADER (estilo auditor) --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10
                    bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
                    px-8 py-8 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)]"></div>

        @php
            $audBadgeMap = [
                'pendiente' => 'border-amber-300/20 bg-amber-500/10 text-amber-200',
                'aprobado' => 'border-emerald-300/20 bg-emerald-500/10 text-emerald-200',
                'rechazado' => 'border-red-300/20 bg-red-500/10 text-red-200',
                'observado' => 'border-amber-300/20 bg-amber-500/10 text-amber-200',
            ];
            $estadoActual = $estado ?? '';
            $estadoLabel = $estadoActual ? ucfirst($estadoActual) : 'Todos';
            $estadoBadge = $audBadgeMap[$estadoActual] ?? 'border-purple-300/20 bg-purple-500/10 text-purple-200';
        @endphp

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Módulo 2</p>
                <h1 class="mt-3 text-3xl font-black text-white">Revisión de comprobantes</h1>
                <p class="mt-2 text-sm text-white/80">
                    Valida facturas, tickets y contratos contra desembolsos e hitos. Prioriza pendientes y revisa evidencia.
                </p>
            </div>

            {{-- Pills (resumen visual) --}}
            <div class="grid gap-3 text-sm text-white sm:grid-cols-2">
                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                        Estado auditoría
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border {{ $estadoBadge }} px-3 py-1 text-xs font-semibold">
                        {{ $estadoLabel }}
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
                <h2 class="mt-1 text-lg font-semibold text-white">Filtrar comprobantes</h2>
                <p class="mt-1 text-sm text-zinc-400">Encuentra por proveedor, proyecto, concepto y estado.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-purple-300/20 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-200">
                    {{ $pagos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pagos->total() : (is_countable($pagos) ? count($pagos) : 0) }}
                    resultados
                </span>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr_240px_auto] items-end">
            <div>
                <label class="block text-xs uppercase tracking-[0.3em] text-zinc-500">Buscar</label>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Proveedor, proyecto o concepto"
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
                <a href="{{ route('auditor.comprobantes') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white
                          hover:bg-white/10">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- LISTADO / “TABLA” --}}
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 shadow-xl overflow-hidden">
        {{-- Header table --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Comprobantes</p>
                <h3 class="mt-1 text-lg font-semibold text-white">Cola de revisión</h3>
                <p class="mt-1 text-sm text-zinc-400">Revisa detalle, evidencia y estado de solicitud.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-white">
                    {{ $pagos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pagos->count() : (is_countable($pagos) ? count($pagos) : 0) }}
                    en vista
                </span>
            </div>
        </div>

        {{-- Column headers (solo escritorio) --}}
        <div class="hidden md:grid md:grid-cols-12 gap-4 px-6 py-3 text-[11px] uppercase tracking-[0.22em] text-zinc-400 font-semibold bg-white/3">
            <div class="md:col-span-2">Comprobante</div>
            <div class="md:col-span-3">Proyecto / Hito</div>
            <div class="md:col-span-2">Monto</div>
            <div class="md:col-span-2">Proveedor</div>
            <div class="md:col-span-2">Fecha / Estado</div>
            <div class="md:col-span-1 text-right">Acción</div>
        </div>

        <div class="px-6 py-4 space-y-3">
            @forelse ($pagos as $pago)
                @php
                    $aud = $pago->estado_auditoria ?? 'pendiente';

                    $audBadge = $audBadgeMap[$aud] ?? 'border-purple-300/20 bg-purple-500/10 text-purple-200';
                @endphp

                <div class="rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-4 items-start md:items-center">
                        {{-- Comprobante --}}
                        <div class="md:col-span-2">
                            <p class="font-semibold text-white">#{{ $pago->id }}</p>
                            <p class="mt-0.5 text-xs text-zinc-400 line-clamp-2">
                                {{ $pago->concepto ?? 'Sin concepto' }}
                            </p>
                            <div class="mt-2 inline-flex items-center rounded-full border {{ $audBadge }} px-2.5 py-1 text-[11px] font-semibold">
                                Auditoría: {{ ucfirst($aud) }}
                            </div>
                        </div>

                        {{-- Proyecto / hito --}}
                        <div class="md:col-span-3">
                            <p class="font-semibold text-white">
                                {{ optional($pago->solicitud->proyecto)->titulo ?? 'Proyecto' }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                Hito: {{ $pago->solicitud->hito ?? 'Hito' }}
                            </p>
                        </div>

                        {{-- Monto --}}
                        <div class="md:col-span-2">
                            <p class="text-lg font-semibold text-purple-200">
                                ${{ number_format($pago->monto, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-zinc-500">Monto declarado</p>
                        </div>

                        {{-- Proveedor --}}
                        <div class="md:col-span-2">
                            <p class="text-sm font-semibold text-white">
                                {{ $pago->proveedor->nombre_proveedor ?? 'Proveedor' }}
                            </p>
                            <p class="text-xs text-zinc-400">Con soporte</p>
                        </div>

                        {{-- Fecha / Estado solicitud --}}
                        <div class="md:col-span-2">
                            <p class="text-sm text-white">
                                {{ $pago->fecha_pago?->format('Y-m-d') ?? 'N/D' }}
                            </p>
                        </div>

                        {{-- Acción --}}
                        <div class="md:col-span-1 md:text-right">
                            <a href="{{ route('auditor.comprobantes.show', $pago) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-purple-500 px-3 py-2 text-xs font-semibold text-white
                                      shadow-lg shadow-purple-900/30 hover:bg-purple-400 w-full md:w-auto">
                                Ver detalle
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-white/10 bg-white/5 px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-white">No hay comprobantes registrados</p>
                    <p class="mt-1 text-xs text-zinc-400">Cuando existan, aparecerán aquí para revisión.</p>
                    <a href="{{ route('auditor.comprobantes') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-white hover:bg-white/10">
                        Recargar
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-5 border-t border-white/10">
            {{ $pagos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pagos->links() : '' }}
        </div>
    </section>

</div>
@endsection
