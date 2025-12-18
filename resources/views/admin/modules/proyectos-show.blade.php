<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detalle de proyecto | CrowdUp Admin</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-950 text-zinc-100 font-sans min-h-screen">
  <!-- Background glow (más azul, menos fucsia) -->
  <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -left-28 -top-24 h-80 w-80 rounded-full bg-indigo-600/25 blur-3xl"></div>
    <div class="absolute right-0 top-24 h-80 w-80 rounded-full bg-sky-500/15 blur-3xl"></div>
    <div class="absolute left-1/2 bottom-[-120px] h-96 w-96 -translate-x-1/2 rounded-full bg-indigo-500/10 blur-3xl"></div>
  </div>

  <header class="sticky top-0 z-30 border-b border-white/10 bg-zinc-950/80 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <a href="{{ route('admin.proyectos') }}" class="inline-flex items-center gap-2 text-sm text-zinc-300 hover:text-white">
          <span aria-hidden="true">&larr;</span> Volver a proyectos
        </a>
        <h1 class="text-lg font-semibold text-white">Detalle de proyecto</h1>
      </div>
      <div class="flex items-center gap-3 text-xs leading-tight">
        <span class="font-semibold text-white">{{ Auth::user()->nombre_completo ?? Auth::user()->name }}</span>
        <span class="text-zinc-400 uppercase tracking-wide">ADMIN</span>
      </div>
    </div>
  </header>

  <main class="w-full">
    <div class="grid gap-6 lg:grid-cols-[280px_1fr] lg:min-h-[calc(100vh-64px)]">
      <!-- Sidebar -->
      <aside class="lg:sticky lg:top-16 lg:h-[calc(100vh-64px)] border-r border-white/10 bg-zinc-950/40">
        @include('admin.partials.modules', ['active' => 'proyectos'])
      </aside>

      <!-- Main content -->
      <div class="lg:h-[calc(100vh-64px)] lg:overflow-y-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

          <!-- HERO / RESUMEN -->
          <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-indigo-600/20 via-zinc-900/80 to-zinc-900/70 p-6 shadow-2xl">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
              <!-- Left: info -->
              <div class="space-y-4">
                <div class="space-y-2">
                  <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-200">Proyecto</p>
                  <h2 class="text-3xl font-bold text-white leading-tight">{{ $proyecto->titulo }}</h2>

                  <div class="flex flex-wrap items-center gap-3 text-sm text-zinc-300">
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-zinc-100">
                      {{ strtoupper($proyecto->estado ?? 'pendiente') }}
                    </span>

                    <span class="text-zinc-400">Creado {{ $proyecto->created_at?->format('d/m/Y') }}</span>

                    @if($proyecto->creador)
                      <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-zinc-200">{{ $proyecto->creador->nombre_completo ?? $proyecto->creador->name }}</span>
                      </span>

                      @if($proyecto->creador->estado_verificacion)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-200 border border-emerald-500/20">
                          <span class="h-2 w-2 rounded-full bg-emerald-300"></span> Verificado KYC
                        </span>
                      @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-1 text-[11px] font-semibold text-amber-200 border border-amber-500/20">
                          <span class="h-2 w-2 rounded-full bg-amber-300"></span> KYC pendiente
                        </span>
                      @endif
                    @endif
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm text-white/90">
                  <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-white/60">Categoría</p>
                    <p class="mt-1 font-semibold text-white">{{ $proyecto->categoria ?? 'N/D' }}</p>
                    <p class="mt-1 text-[12px] text-white/60">Ubicación: {{ $proyecto->ubicacion_geografica ?? 'N/D' }}</p>
                  </div>
                  <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-white/60">Modelo</p>
                    <p class="mt-1 font-semibold text-white">{{ $proyecto->modelo_financiamiento ?? 'N/D' }}</p>
                    <p class="mt-1 text-[12px] text-white/60">Fecha límite: {{ optional($proyecto->fecha_limite)->format('d/m/Y') ?? 'Sin fecha' }}</p>
                  </div>
                </div>

                @if($proyecto->descripcion_proyecto)
                  <!-- Sin JS: colapsable -->
                  <details class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <summary class="cursor-pointer text-sm font-semibold text-white/90 select-none">
                      Descripción del proyecto
                      <span class="ml-2 text-xs text-white/50">(ver)</span>
                    </summary>
                    <p class="mt-3 text-sm text-white/80 leading-relaxed">
                      {{ $proyecto->descripcion_proyecto }}
                    </p>
                  </details>
                @endif

                <!-- Acciones: 1 primaria + secundarias -->
                <div class="flex flex-wrap gap-2">
                  <a href="{{ route('admin.proyectos.gastos', $proyecto) }}"
                     class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white border border-indigo-500/50 hover:bg-indigo-500">
                    Panel financiero
                  </a>

                  <a href="{{ route('admin.proveedores') }}?proyecto={{ $proyecto->id }}"
                     class="inline-flex items-center justify-center rounded-xl bg-white/5 px-4 py-2 text-xs font-semibold text-zinc-100 border border-white/10 hover:bg-white/10">
                    Pagos a proveedores
                  </a>

                  <a href="{{ route('auditor.reportes') }}?q={{ urlencode($proyecto->titulo) }}"
                     class="inline-flex items-center justify-center rounded-xl bg-white/5 px-4 py-2 text-xs font-semibold text-zinc-100 border border-white/10 hover:bg-white/10">
                    Reportes sospechosos
                  </a>

                  <form method="POST" action="{{ route('admin.proyectos.publicacion', $proyecto) }}" class="inline-flex flex-wrap gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="accion" value="permitir" id="admin-accion-publicacion">
                    <button type="submit" onclick="document.getElementById('admin-accion-publicacion').value='permitir'"
                      class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white border border-emerald-500/60 hover:bg-emerald-500">
                      Permitir publicación
                    </button>
                    <button type="submit" onclick="document.getElementById('admin-accion-publicacion').value='pausar'"
                      class="inline-flex items-center justify-center rounded-xl bg-amber-600/80 px-4 py-2 text-xs font-semibold text-white border border-amber-500/60 hover:bg-amber-500">
                      Pausar proyecto
                    </button>
                  </form>
                </div>
              </div>

              <!-- Right: micro-resumen -->
              <div class="rounded-3xl border border-white/10 bg-zinc-950/30 p-5 shadow-xl space-y-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Resumen</p>
                  <h3 class="mt-1 text-lg font-semibold text-white">Estado financiero y control</h3>
                  <p class="mt-1 text-xs text-zinc-500">Vista rápida para decisiones</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-white/60">Transparencia</p>
                    <p class="mt-2 text-2xl font-extrabold text-emerald-200 tabular-nums">{{ $riesgos['transparencia'] ?? 0 }}%</p>
                    <div class="mt-2 h-2 w-full rounded-full bg-white/10 overflow-hidden">
                      <div class="h-full rounded-full bg-emerald-400" style="width: {{ min($riesgos['transparencia'] ?? 0, 100) }}%"></div>
                    </div>
                    <p class="mt-2 text-[11px] text-white/55">Fondos con comprobantes</p>
                  </div>

                  <div class="rounded-2xl border border-amber-400/30 bg-amber-500/10 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-amber-100/80">Reportes abiertos</p>
                    <p class="mt-2 text-2xl font-extrabold text-amber-200 tabular-nums">{{ $riesgos['reportes_abiertos'] ?? 0 }}</p>
                    <p class="mt-1 text-[11px] text-amber-100/80">De {{ $riesgos['reportes_totales'] ?? 0 }} totales</p>
                  </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-[11px] uppercase tracking-[0.3em] text-white/60">Hitos y avances</p>
                      <p class="text-xs text-white/55">Reportado vs planificado</p>
                    </div>
                    <span class="text-[11px] rounded-full border border-white/15 bg-white/5 px-3 py-1 text-zinc-100 tabular-nums">
                      {{ $hitosCumplidos }} / {{ $planificados }}
                    </span>
                  </div>
                  <div class="mt-3 h-2 w-full rounded-full bg-white/10 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 via-indigo-400 to-sky-400 shadow-[0_0_12px_rgba(79,70,229,0.4)]"
                         style="width: {{ $progresoHitos }}%;"></div>
                  </div>
                  <div class="mt-2 flex items-center justify-between text-[11px] text-white/60">
                    <span>Progreso</span>
                    <span class="text-white font-semibold tabular-nums">{{ $progresoHitos }}%</span>
                  </div>
                  @if ($planificados === 0)
                    <p class="mt-2 text-xs text-amber-200">Sin cronograma cargado para este proyecto.</p>
                  @endif
                </div>
              </div>
            </div>
          </section>

          <!-- KPI BAND (separado para que el hero respire) -->
          @php
            $kpisMain = [
              ['label' => 'Meta', 'value' => 'US$ '.number_format($proyecto->meta_financiacion, 2)],
              ['label' => 'Recaudado', 'value' => 'US$ '.number_format($stats['total_recaudado'] ?? 0, 2)],
              ['label' => '% Avance', 'value' => $proyecto->meta_financiacion > 0 ? round(($stats['total_recaudado'] ?? 0)/$proyecto->meta_financiacion*100).'%' : '0%'],
            ];
            $kpisSecondary = [
              ['label' => 'Fondos retenidos', 'value' => 'US$ '.number_format($fondos['retenidos'] ?? 0, 2)],
              ['label' => 'Fondos liberados', 'value' => 'US$ '.number_format($fondos['liberados'] ?? 0, 2)],
              ['label' => 'Fondos gastados', 'value' => 'US$ '.number_format($fondos['gastado'] ?? 0, 2)],
            ];
          @endphp

          <section class="rounded-3xl border border-white/10 bg-zinc-900/60 p-6 shadow-xl">
            <div class="flex items-end justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Indicadores</p>
                <h3 class="mt-1 text-lg font-semibold text-white">KPIs del proyecto</h3>
              </div>
              <p class="text-xs text-zinc-500">Números clave del flujo de fondos</p>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-3">
              @foreach ($kpisMain as $kpi)
                <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                  <p class="text-[11px] uppercase tracking-[0.3em] text-white/60">{{ $kpi['label'] }}</p>
                  <p class="mt-2 text-3xl font-extrabold text-white tabular-nums">{{ $kpi['value'] }}</p>
                </div>
              @endforeach
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-3">
              @foreach ($kpisSecondary as $kpi)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                  <p class="text-[11px] uppercase tracking-[0.3em] text-white/60">{{ $kpi['label'] }}</p>
                  <p class="mt-2 text-xl font-bold text-white tabular-nums">{{ $kpi['value'] }}</p>
                </div>
              @endforeach
            </div>
          </section>

          <!-- CONTENIDO: Movimientos + Riesgos -->
          <section class="grid gap-6 lg:grid-cols-3">
            <!-- Movimientos -->
            <div class="lg:col-span-2 rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Movimientos recientes</p>
                  <h3 class="mt-1 text-lg font-semibold text-white">Aportes, desembolsos y pagos</h3>
                  <p class="mt-1 text-xs text-zinc-500">Últimos 5 registros por módulo</p>
                </div>
                <a href="{{ route('admin.proyectos.gastos', $proyecto) }}"
                   class="inline-flex items-center justify-center rounded-xl bg-white/5 px-4 py-2 text-xs font-semibold text-zinc-100 border border-white/10 hover:bg-white/10">
                  Ver todo
                </a>
              </div>

              <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <!-- Aportes -->
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                  <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <p class="text-[12px] uppercase tracking-[0.28em] text-zinc-400">Aportes</p>
                    <span class="text-[11px] text-zinc-500">{{ $aportacionesTotal }}</span>
                  </div>
                  <div class="max-h-80 overflow-auto divide-y divide-white/5">
                    @forelse($aportacionesRecientes as $aporte)
                      <div class="flex items-center justify-between px-4 py-3">
                        <div class="space-y-1 min-w-0">
                          <p class="text-sm text-white truncate">
                            {{ $aporte->colaborador->nombre_completo ?? $aporte->colaborador->name ?? 'Colaborador' }}
                          </p>
                          <p class="text-xs text-zinc-400">Fecha {{ $aporte->fecha_aportacion?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right shrink-0">
                          <p class="text-sm font-semibold text-emerald-300 tabular-nums">+ US$ {{ number_format($aporte->monto, 2) }}</p>
                          <p class="text-xs text-zinc-400">{{ strtoupper($aporte->estado_pago) }}</p>
                        </div>
                      </div>
                    @empty
                      <p class="py-6 text-sm text-zinc-400 text-center">Sin aportaciones registradas.</p>
                    @endforelse
                  </div>
                </div>

                <!-- Desembolsos -->
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                  <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <p class="text-[12px] uppercase tracking-[0.28em] text-zinc-400">Desembolsos</p>
                    <span class="text-[11px] text-zinc-500">{{ $desembolsosTotal }}</span>
                  </div>
                  <div class="max-h-80 overflow-auto divide-y divide-white/5">
                    @forelse($desembolsosRecientes as $sol)
                      <div class="flex items-center justify-between px-4 py-3">
                        <div class="space-y-1 min-w-0">
                          <p class="text-sm text-white truncate">{{ $sol->hito ?? 'Hito' }}</p>
                          <p class="text-xs text-zinc-400">Estado {{ ucfirst($sol->estado) }} • {{ $sol->created_at?->format('d/m/Y') }}</p>
                        </div>
                        <p class="text-sm font-semibold text-amber-200 tabular-nums shrink-0">US$ {{ number_format($sol->monto_solicitado, 2) }}</p>
                      </div>
                    @empty
                      <p class="py-6 text-sm text-zinc-400 text-center">Sin desembolsos registrados.</p>
                    @endforelse
                  </div>
                </div>

                <!-- Pagos -->
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden md:col-span-2 xl:col-span-1">
                  <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <p class="text-[12px] uppercase tracking-[0.28em] text-zinc-400">Proveedores</p>
                    <span class="text-[11px] text-zinc-500">{{ $pagosTotal }}</span>
                  </div>
                  <div class="max-h-80 overflow-auto divide-y divide-white/5">
                    @forelse($pagosRecientes as $pago)
                      <div class="flex items-center justify-between px-4 py-3">
                        <div class="space-y-1 min-w-0">
                          <p class="text-sm text-white truncate">{{ $pago->proveedor->nombre_proveedor ?? 'Proveedor' }}</p>
                          <p class="text-xs text-zinc-400">Fecha {{ $pago->fecha_pago?->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right shrink-0">
                          <p class="text-sm font-semibold text-emerald-200 tabular-nums">US$ {{ number_format($pago->monto, 2) }}</p>
                          <p class="text-xs text-zinc-400">{{ ucfirst($pago->estado_auditoria ?? 'pendiente') }}</p>
                        </div>
                      </div>
                    @empty
                      <p class="py-6 text-sm text-zinc-400 text-center">Sin pagos registrados.</p>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>

            <!-- Riesgos -->
            <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl space-y-4">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Riesgos y auditoría</p>
                  <h3 class="mt-1 text-lg font-semibold text-white">Estado de cumplimiento</h3>
                </div>

                <a href="{{ route('admin.proyectos.gastos', $proyecto) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white border border-indigo-500/50 hover:bg-indigo-500">
                  Panel financiero
                </a>
              </div>

              <div class="space-y-3">
                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                  <div>
                    <p class="text-sm font-semibold text-white">Reportes sospechosos</p>
                    <p class="text-xs text-zinc-400">Abiertos / Totales</p>
                  </div>
                  <p class="text-xl font-bold text-amber-200 tabular-nums">
                    {{ $riesgos['reportes_abiertos'] ?? 0 }} / {{ $riesgos['reportes_totales'] ?? 0 }}
                  </p>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                  <div>
                    <p class="text-sm font-semibold text-white">Gastos observados/rechazados</p>
                    <p class="text-xs text-zinc-400">Pagos con incidencia</p>
                  </div>
                  <p class="text-xl font-bold text-red-200 tabular-nums">{{ $riesgos['pagos_observados'] ?? 0 }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                  <p class="text-sm font-semibold text-white">Transparencia</p>
                  <p class="mt-1 text-2xl font-bold text-emerald-200 tabular-nums">{{ $riesgos['transparencia'] ?? 0 }}%</p>
                  <p class="text-xs text-zinc-400">Fondos con comprobantes/gastos</p>
                  <div class="mt-3 h-2 w-full rounded-full bg-white/10 overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-400" style="width: {{ min($riesgos['transparencia'] ?? 0, 100) }}%"></div>
                  </div>
                </div>
              </div>

              <!-- Tip / alerta -->
              <div class="rounded-2xl border border-indigo-500/20 bg-indigo-600/10 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-200/80">Tip</p>
                <p class="mt-2 text-sm text-white/80">
                  Si ves anomalías, revisa el panel financiero y cruza comprobantes con pagos a proveedores.
                </p>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </main>
</body>
</html>
