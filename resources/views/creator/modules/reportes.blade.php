@extends('creator.layouts.panel')

@section('title', 'Reportes y comprobantes')
@section('active', 'reportes')

@section('content')
@php
    $selectedProject = $proyectos->firstWhere('id', $selectedProjectId);
    $projectMeta = $selectedProject?->meta_financiacion ?? 0;
    $projectRecaudado = $selectedProject?->monto_recaudado ?? 0;
    $projectProgress = $projectMeta ? min(100, round(($projectRecaudado / $projectMeta) * 100)) : 0;
    $liberadoTotal = $solicitudes->whereIn('estado', ['aprobado', 'liberado', 'pagado', 'gastado'])->sum('monto_solicitado');
    $justificadoTotal = $pagos->sum('monto');
    $comprobantesPendientes = max($resumen['pagosProveedor'] - $resumen['pagosConAdjuntos'], 0);
    $solicitudSaldo = $solicitudes->mapWithKeys(function ($sol) use ($pagos) {
        $gastado = $pagos->where('solicitud_id', $sol->id)->sum('monto');
        return [$sol->id => max($sol->monto_solicitado - $gastado, 0)];
    });
    $defaultSaldo = $solicitudes->first() ? ($solicitudSaldo[$solicitudes->first()->id] ?? 0) : 0;
    $formDisabled = $solicitudes->isEmpty();
@endphp

<div class="space-y-6 px-4 sm:px-6 lg:px-8">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-emerald-600/25 via-zinc-900/70 to-zinc-900/70 p-8 shadow-2xl ring-1 ring-indigo-500/10 space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Reportes y comprobantes</p>
                <h2 class="text-2xl font-bold text-white">Pagos asociados a desembolsos aprobados</h2>
                <p class="text-sm text-zinc-400">Sube facturas, comprueba cada pago y mira qué desembolso se va consumiendo.</p>
            </div>
            <div class="flex flex-col items-start gap-2 text-xs text-zinc-300 lg:items-end">
                <a href="{{ route('auditor.reportes') }}" class="hover:text-white">Ver reportes de auditoría</a>
                <a href="{{ route('creador.fondos.historial', ['proyecto' => $selectedProjectId]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white shadow-lg shadow-emerald-900/40 hover:bg-emerald-500">
                    Ver desembolsos
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('creador.reportes') }}" class="grid gap-3 md:grid-cols-[1.3fr,0.7fr]">
            <div>
                <label class="text-xs text-zinc-400">Proyecto</label>
                <select name="proyecto" class="mt-1 w-full appearance-none rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                    @forelse ($proyectos as $proyecto)
                        <option value="{{ $proyecto->id }}" @selected($selectedProjectId == $proyecto->id)>{{ $proyecto->titulo }}</option>
                    @empty
                        <option value="">Sin proyectos disponibles</option>
                    @endforelse
                </select>
                @if ($selectedProject)
                    <p class="mt-2 text-xs text-zinc-300">
                        Estado: <span class="font-semibold text-white">{{ ucfirst($selectedProject->estado ?? 'borrador') }}</span>
                        · Recaudado: <span class="font-semibold text-emerald-200">USD {{ number_format($projectRecaudado, 2) }}</span>
                        · Meta: USD {{ number_format($projectMeta, 2) }} · Progreso {{ $projectProgress }}%
                    </p>
                @endif
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white hover:border-indigo-400/60">Actualizar</button>
        </form>
    </section>

    @if ($proyectos->isEmpty())
        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-8 text-sm text-zinc-300 shadow-2xl">
            No tienes proyectos creados aun. <a class="text-indigo-300 underline" href="{{ route('creador.proyectos') }}">Crea un proyecto</a> para subir facturas y comprobantes.
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-indigo-500/10 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Resumen</p>
                        <h3 class="text-lg font-bold text-white">Pagos registrados</h3>
                        <p class="text-sm text-zinc-400">Toda la trazabilidad que necesitas para justificar desembolsos.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Total pagado</p>
                        <p class="text-2xl font-bold text-white">USD {{ number_format($resumen['totalPagado'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Pagos con adjuntos</p>
                        <p class="text-2xl font-bold text-emerald-200">{{ $resumen['pagosConAdjuntos'] }}</p>
                    </div>
                    <div class="rounded-2xl border {{ $comprobantesPendientes ? 'border-amber-400/40 bg-amber-500/10 text-amber-100' : 'border-white/10 bg-white/5' }} p-4">
                        <p class="text-[11px] uppercase text-zinc-400">Comprobantes pendientes</p>
                        <p class="text-2xl font-bold {{ $comprobantesPendientes ? 'text-amber-200' : 'text-white' }}">{{ $comprobantesPendientes }}</p>
                        @if ($comprobantesPendientes)
                            <p class="text-[10px] text-amber-200">Sube los comprobantes faltantes para cerrar el ciclo.</p>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Monto justificado / desembolsos liberados</p>
                        <p class="text-2xl font-bold text-emerald-200">USD {{ number_format($justificadoTotal, 2) }} / USD {{ number_format($liberadoTotal, 2) }}</p>
                        <p class="text-[11px] text-zinc-500 mt-1">Transparencia visible al instante.</p>
                    </div>
                </div>

                <div class="space-y-3" id="pagos-registrados">
                    @php
                        $estadoBadges = [
                            'aprobado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                            'liberado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                            'pagado' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30',
                            'gastado' => 'bg-sky-500/15 text-sky-100 border border-sky-400/30',
                            'rechazado' => 'bg-red-500/15 text-red-100 border border-red-400/30',
                            'pendiente' => 'bg-amber-500/15 text-amber-100 border border-amber-400/30',
                        ];
                    @endphp
                    @forelse ($pagos as $pago)
                        @php
                            $estadoAuditoria = $pago->estado_auditoria ?? 'pendiente';
                            $badgeAuditoria = $estadoBadges[$estadoAuditoria] ?? 'bg-white/10 text-white border border-white/20';
                        @endphp
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ $pago->concepto ?? 'Pago a proveedor' }}</p>
                                    <p class="text-xs text-zinc-400">{{ $pago->fecha_pago?->format('d/m/Y') ?? 'Fecha pendiente' }} · Proveedor: {{ $pago->proveedor->nombre_proveedor ?? 'N/D' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeAuditoria }}">Auditoría: {{ ucfirst($estadoAuditoria) }}</span>
                            </div>
                            <div class="text-sm text-zinc-200">
                                <p>Monto: USD {{ number_format($pago->monto, 2) }} · Hito: {{ $pago->solicitud->hito ?? 'Sin hito' }}</p>
                                <p class="text-xs text-zinc-500">Desembolso: {{ $pago->solicitud->monto_solicitado ? 'USD '.number_format($pago->solicitud->monto_solicitado, 2) : 'Sin monto registrado' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-300">
                                @if (!empty($pago->adjuntos))
                                    <span>Archivos:</span>
                                    @foreach ($pago->adjuntos as $idx => $archivo)
                                        <a href="{{ asset('storage/' . $archivo) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-3 py-1 hover:border-emerald-400/60">
                                            Archivo {{ $idx + 1 }}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-zinc-500">Sin adjuntos</span>
                                @endif
                                <a href="{{ route('creador.reportes.pagos.show', $pago) }}" class="ml-auto text-emerald-300 underline">Ver detalle</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-zinc-400">Sin pagos registrados para este proyecto.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-emerald-500/10 space-y-5">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Nuevo pago</p>
                    <h3 class="text-lg font-bold text-white">Sube factura y comprobantes</h3>
                    <p class="text-sm text-zinc-400">Relaciona cada pago con un desembolso aprobado y un proveedor.</p>
                </div>

                <div class="space-y-1 text-sm text-zinc-300">
                    <p>Origen del pago: <span class="font-semibold text-white">{{ $selectedProject->titulo ?? 'Proyecto' }}</span></p>
                    <p>Fondos justificados hasta ahora: <span class="font-semibold text-emerald-200">USD {{ number_format($justificadoTotal, 2) }}</span></p>
                    <p>Desembolsos liberados: <span class="font-semibold text-emerald-200">USD {{ number_format($liberadoTotal, 2) }}</span></p>
                </div>

                @if ($formDisabled)
                    <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        Aún no hay desembolsos aprobados para este proyecto. Solicita un desembolso en el Centro financiero antes de registrar pagos.
                    </div>
                @endif

                <form method="POST" action="{{ route('creador.reportes.pagos.store', $selectedProjectId) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-zinc-300">Desembolso aprobado *</label>
                        <select id="desembolso-select" name="solicitud_id" @disabled($formDisabled) required class="mt-1 w-full rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="">Selecciona un desembolso</option>
                            @foreach ($solicitudes as $solicitud)
                                <option value="{{ $solicitud->id }}" data-saldo="{{ $solicitudSaldo[$solicitud->id] ?? 0 }}" @selected(old('solicitud_id') == $solicitud->id)>
                                    {{ $solicitud->hito ?? 'Hito' }} · {{ ucfirst($solicitud->estado) }} · USD {{ number_format($solicitud->monto_solicitado, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-zinc-500 mt-1">Solo aparecen desembolsos aprobados, liberados o en ejecución.</p>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Proveedor *</label>
                        <select name="proveedor_id" @disabled($formDisabled) required class="mt-1 w-full rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($proveedores as $prov)
                                <option value="{{ $prov->id }}" @selected(old('proveedor_id') == $prov->id)>{{ $prov->nombre_proveedor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Monto *</label>
                        <input type="number" name="monto" step="0.01" min="0.01" value="{{ old('monto') }}" @disabled($formDisabled) required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ej. 450.00">
                        <p id="saldo-disponible" class="text-[11px] text-zinc-400 mt-1">Saldo disponible en el desembolso seleccionado: USD {{ number_format($defaultSaldo, 2) }}</p>
                        <p class="text-[11px] text-zinc-500">No puede superar el saldo disponible del desembolso.</p>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Fecha de pago</label>
                        <input type="date" name="fecha_pago" value="{{ old('fecha_pago') }}" @disabled($formDisabled) class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Concepto</label>
                        <input name="concepto" value="{{ old('concepto') }}" @disabled($formDisabled) class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ej. Pago factura 001 a proveedor">
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Calificación 1-5 (opcional)</label>
                        <input type="number" name="calificacion" min="1" max="5" step="1" value="{{ old('calificacion') }}" @disabled($formDisabled) class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="5">
                        <p class="text-[11px] text-zinc-500 mt-1">Ayuda a construir historial del proveedor.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm text-zinc-300">Adjuntar facturas y comprobantes (PDF, JPG, PNG) *</label>
                        <label for="reportes-adjuntos" class="group flex flex-col gap-2 rounded-xl border-2 border-dashed border-emerald-400/40 bg-emerald-500/5 px-4 py-5 text-sm text-emerald-50 cursor-pointer hover:border-emerald-400 transition @if($formDisabled) opacity-60 cursor-not-allowed @endif">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-100 border border-emerald-500/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold text-white">Arrastra o haz clic para seleccionar</p>
                                    <p class="text-[12px] text-emerald-200/80" data-file-label="reportes-adjuntos">Sin archivos seleccionados</p>
                                </div>
                            </div>
                            <p class="text-[12px] text-emerald-200/70">Obligatorio para validar el pago. Máximo 8MB por archivo.</p>
                            <input id="reportes-adjuntos" type="file" name="adjuntos[]" multiple @disabled($formDisabled) class="hidden" data-file-preview="reportes-adjuntos">
                        </label>
                        <div class="flex flex-wrap gap-2 text-xs text-zinc-300" data-file-list="reportes-adjuntos"></div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" @disabled($formDisabled) class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50">
                            Registrar pago
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>

@if (!$formDisabled)
    <script>
        (() => {
            const select = document.getElementById('desembolso-select');
            const texto = document.getElementById('saldo-disponible');
            const saldos = {!! $solicitudSaldo->isEmpty() ? '{}' : $solicitudSaldo->toJson() !!};
            const formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 });
            const updateSaldo = () => {
                if (!select || !texto) return;
                const selected = select.value;
                const value = saldos[selected] !== undefined ? saldos[selected] : 0;
                texto.textContent = `Saldo disponible en el desembolso seleccionado: ${formatter.format(value)}`;
            };
            select.addEventListener('change', updateSaldo);
            updateSaldo();
        })();

        document.querySelectorAll('input[type="file"][data-file-preview]').forEach(input => {
            const previewId = input.getAttribute('data-file-preview');
            const container = document.querySelector(`[data-file-list="${previewId}"]`);
            const label = document.querySelector(`[data-file-label="${previewId}"]`);
            const updateList = () => {
                if (!container) return;
                container.innerHTML = '';
                const files = Array.from(input.files || []);
                if (!files.length) {
                    container.textContent = 'Sin archivos seleccionados';
                    container.classList.add('text-zinc-500');
                    if (label) {
                        label.textContent = 'Sin archivos seleccionados';
                        label.classList.add('text-emerald-200/80');
                        label.classList.remove('text-white');
                        label.closest('label')?.classList.remove('border-emerald-400');
                    }
                    return;
                }
                container.classList.remove('text-zinc-500');
                files.forEach(file => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white';
                    badge.textContent = file.name;
                    container.appendChild(badge);
                });
                if (label) {
                    label.textContent = files.length === 1 ? files[0].name : `${files.length} archivos seleccionados`;
                    label.classList.remove('text-emerald-200/80');
                    label.classList.add('text-white');
                    label.closest('label')?.classList.add('border-emerald-400');
                }
            };
            input.addEventListener('change', updateList);
            updateList();
        });
    </script>
@endif
@endsection
