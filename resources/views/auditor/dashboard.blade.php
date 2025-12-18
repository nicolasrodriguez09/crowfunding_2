@extends('auditor.layouts.panel')

@section('title', 'Panel de Auditor')
@section('active', 'general')
@section('back_url', '')

@section('content')
<div class="space-y-10 px-4 sm:px-6 lg:px-8">

    {{-- HERO / OVERVIEW (estilo como tu panel de creador, pero tono auditor) --}}
    <section id="overview"
        class="relative overflow-hidden rounded-3xl border border-white/10
               bg-gradient-to-r from-purple-900/90 via-indigo-950/75 to-slate-900/80
               px-8 py-10 shadow-2xl">

        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.18),_transparent_45%)]"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">
                    Auditoría y cumplimiento
                </p>
                <h1 class="mt-3 text-3xl font-black text-white">
                    Centro de revisión
                </h1>
                <p class="mt-3 text-base text-white/80">
                    Prioriza desembolsos, comprobantes y reportes. Revisa evidencia y registra decisiones con trazabilidad.
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('auditor.reportes') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl
                              bg-purple-500 px-5 py-3 text-sm font-semibold text-white
                              shadow-lg shadow-purple-900/60 hover:bg-purple-400">
                        Revisar reportes
                    </a>

                    <a href="{{ route('auditor.desembolsos') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl
                              border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white
                              hover:bg-white/15">
                        Ver desembolsos
                    </a>
                </div>
            </div>

            {{-- Pills tipo “estado rápido” (como creador) --}}
            <div class="grid gap-3 text-sm text-white sm:grid-cols-2">
                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-purple-300"></span>
                        Desembolsos pendientes
                    </span>
                    <span class="font-semibold">{{ $kpis['solicitudes_pendientes'] ?? 0 }}</span>
                </div>

                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Pagos registrados
                    </span>
                    <span class="font-semibold">{{ $kpis['pagos_registrados'] ?? 0 }}</span>
                </div>

                <div class="inline-flex items-center justify-between gap-3 rounded-full bg-white/15 px-4 py-2 backdrop-blur sm:col-span-2">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-fuchsia-200"></span>
                        Reportes por revisar
                    </span>
                    <span class="font-semibold">{{ $kpis['reportes_pendientes'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- MÓDULO / RESUMEN --}}
    <section id="general" class="space-y-4">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Módulo 1</p>
            <h2 class="text-2xl font-bold text-white">Panel general del auditor</h2>
            <p class="text-sm text-zinc-400">KPI y colas operativas basadas en la base de datos.</p>
        </div>

        {{-- KPI CARDS (sobrias como sistema, con detalles morados) --}}
        <div class="grid gap-4 md:grid-cols-4">
            {{-- Card 1 --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 min-h-[170px] flex flex-col justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Desembolsos</p>
                    <h3 class="text-lg font-semibold text-white">Pendientes por validar</h3>
                    <p class="text-xs text-zinc-400">Solicitudes que requieren revisión.</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-semibold text-purple-200">{{ $kpis['solicitudes_pendientes'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full border border-purple-300/25 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-200">
                        Pendiente
                    </span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-white/10">
                    <div class="h-1.5 rounded-full bg-purple-400/80" style="width: 70%"></div>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 min-h-[170px] flex flex-col justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Desembolsos</p>
                    <h3 class="text-lg font-semibold text-white">Aprobados / liberados</h3>
                    <p class="text-xs text-zinc-400">Incluye liberados y pagados.</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-semibold text-purple-200">{{ $kpis['solicitudes_aprobadas'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-zinc-200">
                        OK
                    </span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-white/10">
                    <div class="h-1.5 rounded-full bg-indigo-300/70" style="width: 45%"></div>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 min-h-[170px] flex flex-col justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Comprobantes</p>
                    <h3 class="text-lg font-semibold text-white">Pagos registrados</h3>
                    <p class="text-xs text-zinc-400">Pagos con proveedor y evidencia.</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-semibold text-purple-200">{{ $kpis['pagos_registrados'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full border border-purple-300/25 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-200">
                        Evidencia
                    </span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-white/10">
                    <div class="h-1.5 rounded-full bg-purple-300/70" style="width: 60%"></div>
                </div>
            </div>

            {{-- Card 4 --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 min-h-[170px] flex flex-col justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Reportes</p>
                    <h3 class="text-lg font-semibold text-white">Sospechosos por revisar</h3>
                    <p class="text-xs text-zinc-400">Alertas de gasto o fraude.</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-semibold text-purple-200">{{ $kpis['reportes_pendientes'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full border border-purple-300/25 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-200">
                        Atención
                    </span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-white/10">
                    <div class="h-1.5 rounded-full bg-fuchsia-300/70" style="width: 55%"></div>
                </div>
            </div>
        </div>

        {{-- ZONA DE TRABAJO (2 columnas) --}}
        <section class="grid gap-6 lg:grid-cols-[1.4fr,0.9fr] mt-6">

            {{-- Reportes sospechosos --}}
            <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Reportes</p>
                        <h3 class="text-lg font-semibold text-white">Sospechosos por revisar</h3>
                        <p class="text-xs text-zinc-400 mt-1">Alertas enviadas por colaboradores.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-purple-500/15 px-3 py-1 text-xs font-semibold text-purple-200 border border-purple-300/20">
                            {{ $reportesPendientes->count() }}
                        </span>
                        <a href="{{ route('auditor.reportes') }}" class="text-xs text-purple-200 hover:text-white">
                            Ver todos →
                        </a>
                    </div>
                </div>

                <div class="mt-4 divide-y divide-white/5 rounded-2xl border border-white/5 bg-white/5 overflow-hidden">
                    @forelse ($reportesPendientes as $rep)
                        <a href="{{ route('auditor.reportes') }}"
                           class="block px-4 py-3 hover:bg-white/10 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">
                                        {{ $rep->proyecto->titulo ?? 'Proyecto' }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-zinc-400 truncate">
                                        {{ $rep->motivo ?? 'Reporte recibido' }} · {{ optional($rep->created_at)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full bg-purple-500/15 text-purple-200 border border-purple-300/20 px-2.5 py-1 text-[11px] font-semibold">
                                    Revisar
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm font-semibold text-white">Sin reportes pendientes</p>
                            <p class="mt-1 text-xs text-zinc-400">Cuando haya alertas, aparecerán aquí.</p>
                            <a href="{{ route('auditor.reportes') }}" class="mt-3 inline-flex text-xs font-semibold text-purple-200 hover:text-white">
                                Ver reportes →
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Proyectos bajo auditoría --}}
            <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Proyectos</p>
                        <h3 class="text-lg font-semibold text-white">Bajo auditoría</h3>
                        <p class="text-xs text-zinc-400 mt-1">Últimos proyectos monitoreados.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                        {{ $proyectosActivos->count() }}
                    </span>
                </div>

                <div class="mt-4 space-y-3 text-sm text-zinc-300">
                    @forelse ($proyectosActivos as $proy)
                        <div class="rounded-2xl border border-white/5 bg-white/5 px-4 py-3 hover:bg-white/10 transition">
                            <p class="font-semibold text-white">{{ $proy->titulo }}</p>
                            <p class="text-[11px] text-zinc-400">
                                Estado: {{ $proy->estado ?? 'N/D' }} · {{ $proy->created_at?->format('Y-m-d') }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/10 bg-white/5 px-4 py-6 text-center">
                            <p class="text-sm font-semibold text-white">Sin proyectos</p>
                            <p class="mt-1 text-xs text-zinc-400">Aparecerán cuando existan proyectos monitoreados.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </section>
    </section>

</div>
@endsection
