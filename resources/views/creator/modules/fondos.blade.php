@extends('creator.layouts.panel')

@section('title', 'Fondos y desembolsos')
@section('active', 'fondos')

@section('content')
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
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Proyectos</p>
                <h2 class="text-2xl font-bold text-white">Centro financiero de cada proyecto</h2>
                <p class="text-sm text-zinc-400">Visualiza recaudado, escrow, liberado, gastado y solicita desembolsos guiados.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-300">
                <a href="{{ route('creador.fondos.historial', ['proyecto' => $selectedProjectId]) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-500">
                    Ver historial completo
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('creador.fondos') }}" class="grid gap-3 sm:grid-cols-[1fr,auto] sm:items-end">
            <div>
                <label class="text-xs text-zinc-400">Proyecto</label>
                <select name="proyecto" class="mt-1 w-full appearance-none rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                    @forelse ($proyectos as $proyecto)
                        <option value="{{ $proyecto->id }}" @selected($selectedProjectId == $proyecto->id)>{{ $proyecto->titulo }}</option>
                    @empty
                        <option value="">Sin proyectos disponibles</option>
                    @endforelse
                </select>
                <p class="mt-1 text-xs text-zinc-400">Busca por proyecto y actualiza el resumen financiero para ese caso.</p>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                Ir a movimientos financieros
            </button>
        </form>

        @if ($projectSummary)
            <div class="mt-6 grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
                <article class="rounded-2xl border border-white/15 bg-white/5 p-5 shadow-[0_20px_60px_rgba(0,0,0,0.35)] space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Resumen del proyecto</p>
                            <h3 class="text-xl font-semibold text-white">{{ $selectedProject->titulo }}</h3>
                        </div>
                        <span class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-200">
                            {{ ucfirst($projectSummary['estado']) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <p class="text-sm text-zinc-300">Recaudado vs meta</p>
                        <p class="text-lg font-semibold text-emerald-200">USD {{ number_format($projectSummary['recaudado'], 2) }} · Meta {{ number_format($projectSummary['meta'], 2) }}</p>
                    </div>
                    <div class="h-2 rounded-full bg-white/10">
                        <div class="h-2 rounded-full bg-emerald-400" style="width: {{ $projectSummary['progress'] }}%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-zinc-400">
                        <p>Progreso: {{ $projectSummary['progress'] }}%</p>
                        <p>Próximo hito: {{ $projectSummary['hito'] }}</p>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/15 bg-zinc-900/60 p-5 shadow-[0_20px_60px_rgba(0,0,0,0.35)] space-y-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Indicadores rápidos</p>
                    <div class="space-y-2 text-sm text-white">
                        <div class="flex items-center justify-between">
                            <span>Fondos disponibles</span>
                            <span class="font-semibold text-emerald-200">USD {{ number_format($finanzas['disponible'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Solicitudes pendientes</span>
                            <span class="font-semibold text-amber-200">USD {{ number_format($finanzas['pendiente'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Transparencia</span>
                            <span class="font-semibold text-white">{{ $finanzas['recaudado'] ? round((($finanzas['liberado'] / $finanzas['recaudado']) * 100), 0) : 0 }}%</span>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500">Toda la información se recalcula en la vista para que tengas contexto inmediato.</p>
                </article>
            </div>
        @endif
    </section>

    @if ($proyectos->isEmpty())
        <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-8 text-sm text-zinc-300 shadow-2xl">
            No tienes proyectos creados aun. <a class="text-indigo-300 underline" href="{{ route('creador.proyectos') }}">Crea un proyecto</a> para gestionar fondos.
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-indigo-500/10 space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Dashboard financiero</p>
                        <h3 class="text-lg font-bold text-white">Estado del proyecto</h3>
                        <p class="text-sm text-zinc-400">Revisa lo retenido en escrow, liberado, gastado y lo disponible para solicitar.</p>
                    </div>
                    <div class="text-xs text-zinc-400">Actualizado: {{ now()->format('d/m/Y H:i') }}</div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Total recaudado</p>
                        <p class="text-2xl font-bold text-white">USD {{ number_format($finanzas['recaudado'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Fondos retenidos (escrow)</p>
                        <p class="text-xl font-bold text-amber-100">USD {{ number_format($finanzas['retenido'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Fondos liberados</p>
                        <p class="text-xl font-bold text-emerald-100">USD {{ number_format($finanzas['liberado'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-400/50 bg-gradient-to-br from-emerald-500/15 to-emerald-900/50 p-4 shadow-xl">
                        <p class="text-[11px] text-zinc-200">Fondos disponibles para solicitar</p>
                        <p class="text-2xl font-bold text-emerald-200">USD {{ number_format($finanzas['disponible'], 2) }}</p>
                        <p class="text-[11px] text-zinc-300 mt-2">Este es tu monto utilizable sin exceder la meta.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Fondos gastados</p>
                        <p class="text-xl font-bold text-rose-100">USD {{ number_format($finanzas['gastado'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[11px] text-zinc-400">Solicitudes pendientes</p>
                        <p class="text-xl font-bold text-indigo-100">USD {{ number_format($finanzas['pendiente'], 2) }}</p>
                        <p class="text-[11px] text-zinc-400 mt-1">Pendientes por justificar: USD {{ number_format($finanzas['pendiente'], 2) }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-white">Estado de hitos financieros</p>
                            <p class="text-xs text-zinc-400">Consulta cada solicitud y su hito asociado.</p>
                        </div>
                        <a href="{{ route('creador.fondos.historial', ['proyecto' => $selectedProjectId]) }}" class="text-xs text-emerald-200 hover:text-white">Ver historial completo</a>
                    </div>
                    @if ($mensajePendientes)
                        <p class="mt-2 text-xs text-amber-200">Tienes {{ $mensajePendientes }} solicitud(es) en revisión. Evita duplicar hito.</p>
                    @endif
                    @php
                        $estadoBadges = [
                            'pendiente' => ['badge' => 'bg-amber-500/15 text-amber-100 border border-amber-400/30', 'dot' => 'bg-amber-400'],
                            'aprobado' => ['badge' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30', 'dot' => 'bg-emerald-400'],
                            'liberado' => ['badge' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30', 'dot' => 'bg-emerald-400'],
                            'pagado' => ['badge' => 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/30', 'dot' => 'bg-emerald-400'],
                            'rechazado' => ['badge' => 'bg-red-500/15 text-red-100 border border-red-400/30', 'dot' => 'bg-red-400'],
                            'gastado' => ['badge' => 'bg-sky-500/15 text-sky-100 border border-sky-400/30', 'dot' => 'bg-sky-400'],
                        ];
                    @endphp
                    <div class="mt-4 space-y-3">
                        @forelse ($solicitudes as $solicitud)
                            @php
                                $key = $solicitud->estado ?? 'pendiente';
                                $badge = $estadoBadges[$key]['badge'] ?? 'bg-white/10 text-white border border-white/20';
                                $dot = $estadoBadges[$key]['dot'] ?? 'bg-white';
                            @endphp
                            <article class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                <span class="mt-1 h-3 w-3 rounded-full {{ $dot }}"></span>
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-white font-semibold">{{ $solicitud->hito ?? 'Hito financiero' }}</p>
                                            <p class="text-[12px] text-zinc-400">
                                                Registrado: {{ $solicitud->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                                · Estimada: {{ $solicitud->fecha_estimada?->format('d/m/Y') ?? 'Sin fecha' }}
                                            </p>
                                        </div>
                                        <span class="{{ $badge }}">{{ ucfirst($key) }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-zinc-300">
                                        <span class="text-emerald-200 font-semibold">USD {{ number_format($solicitud->monto_solicitado, 2) }}</span>
                                        <a href="{{ route('creador.fondos.solicitudes.show', $solicitud) }}" class="text-emerald-300 underline">Revisar solicitud</a>
                                    </div>
                                    <p class="text-xs text-zinc-400">Descripción: {{ $solicitud->descripcion ?? 'Sin descripción' }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-zinc-400">Aún no has registrado solicitudes. Publica una desde el panel derecho para iniciar el flujo.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-2xl ring-1 ring-emerald-500/10 space-y-5">
                <div class="flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Solicitar desembolso</p>
                    <h3 class="text-lg font-bold text-white">Crea una solicitud</h3>
                    <p class="text-sm text-zinc-400">Valida monto, hito y adjunta documentación de respaldo.</p>
                </div>

                <div class="space-y-1 text-sm text-zinc-300">
                    <p>Fondos disponibles: <span class="font-semibold text-emerald-200">USD {{ number_format($finanzas['disponible'], 2) }}</span></p>
                    <p>Solicitudes pendientes: <span class="font-semibold text-amber-200">{{ $mensajePendientes }}</span></p>
                    <p>Transparencia: <span class="font-semibold text-white">{{ $finanzas['recaudado'] ? round((($finanzas['liberado'] / $finanzas['recaudado']) * 100), 0) : 0 }}% de fondos justificados</span></p>
                </div>

                @if ($sinFondos)
                    <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                        No tienes fondos disponibles para solicitar nuevos desembolsos. Completa comprobantes o espera nuevos aportes.
                    </div>
                @endif

                @if ($mensajePendientes)
                    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        Tienes {{ $mensajePendientes }} solicitud(es) en revisión. Evita duplicar hitos similares.
                    </div>
                @endif

                <form id="formulario-solicitud" method="POST" action="{{ route('creador.fondos.solicitudes.store', $selectedProjectId) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-zinc-300">Monto solicitado *</label>
                        <input type="number" name="monto_solicitado" step="0.01" min="0" value="{{ old('monto_solicitado') }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ej. 1500.00" required>
                        <p class="text-[11px] text-zinc-500 mt-1">No puede superar los fondos disponibles.</p>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Hito financiero *</label>
                        <input name="hito" value="{{ old('hito') }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ej. Entrega fase beta" required>
                        <p class="text-[11px] text-zinc-500 mt-1">Describe el hito clave para justificar el desembolso.</p>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Descripción del uso</label>
                        <textarea name="descripcion" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Sé específico: qué vas a pagar, a quién, en qué fechas.">{{ old('descripcion') }}</textarea>
                        <p class="text-[11px] text-zinc-500 mt-1">Opcional, pero ayuda a acelerar la aprobación del auditor.</p>
                    </div>
                    <div>
                        <label class="text-sm text-zinc-300">Fecha estimada</label>
                        <input type="date" name="fecha_estimada" value="{{ old('fecha_estimada') }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm text-zinc-300">Adjuntar documentación (cotizaciones, contratos)</label>
                        <label for="fondos-adjuntos" class="group flex flex-col gap-2 rounded-xl border-2 border-dashed border-emerald-400/40 bg-emerald-500/5 px-4 py-5 text-sm text-emerald-50 cursor-pointer hover:border-emerald-400 transition">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-100 border border-emerald-500/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold text-white">Arrastra o haz clic para seleccionar</p>
                                    <p class="text-[12px] text-emerald-200/80" data-file-label="fondos-adjuntos">Sin archivos seleccionados</p>
                                </div>
                            </div>
                            <p class="text-[12px] text-emerald-200/70">Opcional. Máx. 8MB por archivo.</p>
                            <input id="fondos-adjuntos" type="file" name="adjuntos[]" multiple class="hidden" data-file-preview="fondos-adjuntos">
                        </label>
                        <div class="flex flex-wrap gap-2 text-xs text-zinc-300" data-file-list="fondos-adjuntos"></div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" @disabled($sinFondos) class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50">
                            Enviar solicitud
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="file"][data-file-preview]').forEach(input => {
        const previewId = input.getAttribute('data-file-preview');
        const container = document.querySelector(`[data-file-list="${previewId}"]`);
        const label = document.querySelector(`[data-file-label="${previewId}"]`);

        const renderFiles = () => {
            const files = Array.from(input.files || []);
            if (!container) return;
            container.innerHTML = '';
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

        input.addEventListener('change', renderFiles);
        renderFiles();
    });
});
</script>
@endpush
