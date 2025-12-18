@extends('auditor.layouts.panel')

@section('title', 'Proyecto')
@section('active', 'proyectos')
@section('back_url', route('auditor.proyectos'))
@section('back_label', 'Volver a proyectos')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 pt-6 space-y-6">
        <div class="rounded-[22px] border border-white/10 bg-zinc-950/80 shadow-2xl overflow-hidden admin-accent-card">
            <div class="relative h-64 bg-gradient-to-r from-purple-800/40 via-purple-700/25 to-fuchsia-600/25">
                @if ($portadaUrl ?? false)
                    <img src="{{ $portadaUrl }}" alt="Portada proyecto" class="h-full w-full object-cover opacity-80">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/35 to-transparent"></div>
                <div class="absolute bottom-4 left-4 right-4 flex flex-col gap-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Proyecto</p>
                    <h1 class="text-3xl font-extrabold text-white">{{ $proyecto->titulo }}</h1>
                    <p class="text-sm text-white/80">Estado: {{ $proyecto->estado ?? 'pendiente' }}</p>
                </div>
            </div>

            <div class="p-6 grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div class="space-y-4">
                    <p class="text-sm text-zinc-300 leading-relaxed">{{ $proyecto->descripcion_proyecto ?? 'Sin descripción' }}</p>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Meta</p>
                            <p class="text-xl font-bold text-white">${{ number_format($proyecto->meta_financiacion, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Recaudado</p>
                            <p class="text-xl font-bold text-emerald-200">${{ number_format($proyecto->monto_recaudado ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Categoría</p>
                            <p class="text-sm font-semibold text-white">{{ $proyecto->categoria ?? 'Sin categoría' }}</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-4 space-y-1">
                        <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Ubicación</p>
                        <p class="text-sm text-white">{{ $proyecto->ubicacion_geografica ?? 'No especificada' }}</p>
                        <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500 pt-2">Creado</p>
                        <p class="text-sm text-white">{{ $proyecto->created_at?->format('Y-m-d') }}</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Cronograma</p>
                                <p class="text-sm text-white">Hitos planificados del proyecto</p>
                            </div>
                            <span class="text-[11px] rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-zinc-200">
                                {{ $cronograma->count() }} hitos
                            </span>
                        </div>
                        @if ($cronograma->isEmpty())
                            <p class="text-sm text-zinc-400">No hay cronograma cargado para este proyecto.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($cronograma as $hito)
                                    <div class="flex items-start gap-3 rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                                        <div class="mt-0.5">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/20 text-indigo-100 border border-indigo-400/40 text-sm font-semibold">
                                                {{ $hito['numero'] ?? $loop->iteration }}
                                            </span>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <p class="text-sm font-semibold text-white">{{ $hito['titulo'] ?? 'Hito' }}</p>
                                            @if (!empty($hito['descripcion']))
                                                <p class="text-xs text-zinc-400">{{ $hito['descripcion'] }}</p>
                                            @endif
                                            <div class="flex items-center gap-2 text-[11px] text-zinc-400">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-black/30 px-2 py-1 border border-white/10">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M6 2.75A.75.75 0 016.75 2h6.5a.75.75 0 010 1.5h-.75v1a.75.75 0 01-1.5 0v-1h-3.5v1a.75.75 0 01-1.5 0v-1H6.75A.75.75 0 016 2.75zM5 6.5a.5.5 0 00-.5.5v8.25c0 .69.56 1.25 1.25 1.25h8.5c.69 0 1.25-.56 1.25-1.25V7a.5.5 0 00-.5-.5H5zm6.25 3a.75.75 0 00-1.5 0v3.5a.75.75 0 001.5 0v-3.5z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $hito['fecha'] ?? 'Sin fecha' }}
                                                </span>
                                                @if (!empty($hito['monto']))
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 text-emerald-100 px-2 py-1 border border-emerald-400/30">
                                                        ${{ number_format((float) $hito['monto'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl border border-white/10 bg-zinc-900/70 p-4">
                        <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Acciones</p>
                        <form method="POST" action="{{ route('auditor.proyectos.publicacion', $proyecto) }}" class="flex flex-wrap gap-2 pt-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="accion" value="permitir" id="accion-publicacion">
                            <button type="submit" onclick="document.getElementById('accion-publicacion').value='permitir'" class="admin-btn admin-btn-primary text-xs w-full">Permitir publicación</button>
                            <button type="submit" onclick="document.getElementById('accion-publicacion').value='pausar'" class="admin-btn admin-btn-ghost text-xs w-full">Pausar publicación</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
