@extends('admin.layouts.panel')

@section('title', 'Auditorias')
@section('active', 'auditorias')

@section('content')
    <div class="space-y-8">
        @php
            $resumenData = $resumen ?? [];
            $reportesAbiertos = $resumenData['reportes_abiertos'] ?? ($reportesPendientes->count() ?? 0);
            $reportesTotales = $resumenData['reportes_totales'] ?? ($reportesAbiertos + ($resumenData['reportes_cerrados_30d'] ?? 0));
            $reportesCerrados30 = $resumenData['reportes_cerrados_30d'] ?? 0;
            $proyectosRevision = $proyectosRevision ?? collect();
            $incidenciasGraves = $resumenData['incidencias_graves'] ?? 0;
            $gastosValidados = $resumenData['gastos_validados'] ?? 0;
            $gastosTotales = $resumenData['gastos_totales'] ?? max(1, $gastosValidados);
            $porcentajeValidados = $gastosTotales > 0 ? round(($gastosValidados / $gastosTotales) * 100) : 0;
            $gastosConComprobante = $resumenData['gastos_con_comprobante'] ?? 0;
            $gastosSinComprobante = $resumenData['gastos_sin_comprobante'] ?? 0;
            $gastosEnRevision = $resumenData['gastos_en_revision'] ?? 0;
            $actividadTimeline = $actividadTimeline ?? [];
            $alertasRapidas = [];

            if (($reportesPendientes ?? collect())->count() > 0) {
                $alertasRapidas[] = 'Hay ' . ($reportesPendientes->count() ?? 0) . ' reportes sospechosos abiertos';
            }
            if (($pagosObservados ?? collect())->count() > 0) {
                $alertasRapidas[] = 'Pagos observados/rechazados detectados en comprobantes';
            }
            if ($gastosSinComprobante > 0) {
                $alertasRapidas[] = $gastosSinComprobante . ' gastos sin comprobante';
            }
            if ($incidenciasGraves > 0) {
                $alertasRapidas[] = $incidenciasGraves . ' incidencias graves en auditoria';
            }
        @endphp

        <section class="rounded-3xl border border-white/10 bg-zinc-900/75 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="border-b border-white/5 px-6 py-6 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-zinc-400">Auditorias y cumplimiento</p>
                <h2 class="text-2xl font-bold text-white">Overview de salud de auditoria</h2>
                <p class="text-sm text-zinc-400">Instantanea de riesgos, carga de revision y documentacion para actuar rapido.</p>
            </div>
            <div class="px-6 py-6 grid gap-4 lg:grid-cols-4 md:grid-cols-2">
                @php
                    $kpis = [
                        ['label' => 'Reportes abiertos', 'value' => $reportesAbiertos, 'href' => route('admin.reportes', ['estado' => 'pendiente']), 'badge' => 'Riesgo'],
                        ['label' => 'Cerrados 30d', 'value' => $reportesCerrados30, 'href' => route('admin.reportes', ['estado' => 'aprobado']), 'badge' => 'Velocidad'],
                        ['label' => 'Proyectos en revision', 'value' => $proyectosRevision->count() ?? 0, 'href' => route('admin.proyectos', ['estado' => 'borrador']), 'badge' => 'Pipeline'],
                        ['label' => '% gastos validados', 'value' => $porcentajeValidados . '%', 'href' => route('admin.gastos', ['estado_auditoria' => 'aprobado']), 'badge' => 'Cumplimiento'],
                    ];
                @endphp
                @foreach ($kpis as $kpi)
                    <a href="{{ $kpi['href'] }}" class="rounded-2xl border border-white/10 bg-gradient-to-br from-indigo-300/60 to-purple-500/40 p-[1px] shadow-lg hover:scale-[1.01] transition">
                        <div class="h-full rounded-2xl bg-zinc-950/90 p-5 space-y-2">
                            <div class="flex items-center justify-between text-[11px] uppercase tracking-[0.28em] text-white/70">
                                <span>{{ $kpi['label'] }}</span>
                                <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px]">{{ $kpi['badge'] }}</span>
                            </div>
                            <p class="mt-1 text-3xl font-extrabold text-white leading-tight">{{ $kpi['value'] }}</p>
                            <p class="text-xs text-white/80">Ver detalle</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <article class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Estado de documentacion</p>
                        <h3 class="text-lg font-semibold text-white">Gastos y comprobantes</h3>
                    </div>
                    <a href="{{ route('admin.gastos', ['estado_auditoria' => 'pendiente']) }}" class="admin-btn admin-btn-primary text-xs">Ver lista de gastos en revision</a>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-emerald-200">Con comprobante ✔</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $gastosConComprobante }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-red-200">Sin comprobante ✖</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $gastosSinComprobante }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-amber-200">En revision 🕵️</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $gastosEnRevision }}</p>
                    </div>
                </div>
                <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/70">Alertas rapidas</p>
                    <div class="mt-3 space-y-2">
                        @forelse($alertasRapidas as $alerta)
                            <div class="flex items-start gap-2 rounded-xl border border-white/10 bg-zinc-950/80 px-3 py-2 text-sm">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-300"></span>
                                <span>{{ $alerta }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400">Sin alertas criticas por ahora.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Actividad reciente</p>
                        <h3 class="text-lg font-semibold text-white">Timeline</h3>
                    </div>
                    <a href="{{ route('admin.auditorias.actividad') }}" class="text-xs text-indigo-200 underline">Ver mas</a>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse($actividadTimeline as $evento)
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-indigo-300"></span>
                            <div class="space-y-1">
                                <p class="text-sm text-white">{{ $evento['mensaje'] ?? '' }}</p>
                                <p class="text-[11px] uppercase tracking-wide text-zinc-400">{{ $evento['timestamp'] ?? '' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-400">Aun no hay actividad reciente registrada.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Reportes sospechosos</p>
                    <h3 class="text-xl font-semibold text-white">Ir a la cola de reportes</h3>
                    <p class="text-sm text-zinc-400">Consulta y gestiona todos los reportes pendientes, aprobados o rechazados.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.reportes') }}" class="admin-btn admin-btn-primary text-xs">Ver reportes sospechosos</a>
                </div>
            </div>
            <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/70">Reportes abiertos</p>
                    <p class="text-2xl font-bold text-white">{{ $reportesAbiertos }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-400">Total en sistema</p>
                    <p class="text-lg font-semibold text-white">{{ $reportesTotales }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
