@extends('admin.layouts.panel')

@section('title', 'Verificaciones')
@section('active', 'verificaciones')

@section('content')
@php
  /**
   * Paleta azul: indigo + sky sobre dark zinc.
   * - Primary: Indigo
   * - Secondary: Sky
   * - Surfaces: zinc + white/5
   */

  $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white border border-indigo-500/40 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/60';

  $btnGhost   = 'inline-flex items-center justify-center gap-2 rounded-xl bg-white/5 px-4 py-2.5 text-sm font-semibold text-zinc-100 border border-white/10 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-indigo-400/40';

  // Acciones (todo azulito):
  // Aprobar = azul sólido
  $btnApprove = 'inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white border border-sky-500/40 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-400/60';
  // Rechazar = outline azul (no rojo)
  $btnReject  = 'inline-flex items-center justify-center gap-2 rounded-xl bg-transparent px-4 py-2.5 text-sm font-semibold text-sky-100 border border-sky-400/30 hover:bg-sky-500/10 focus:outline-none focus:ring-2 focus:ring-sky-400/50';

  $input = 'mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400';

  $badge = function($estado) {
    return match($estado) {
      // Pendiente: azul cielo suave
      'pendiente' => 'inline-flex items-center gap-2 rounded-full bg-sky-500/10 text-sky-200 border border-sky-500/25 px-3 py-1 text-[11px] font-semibold',
      // Aprobada: indigo (brand)
      'aprobada'  => 'inline-flex items-center gap-2 rounded-full bg-indigo-500/10 text-indigo-200 border border-indigo-500/25 px-3 py-1 text-[11px] font-semibold',
      // Rechazada: azul grisáceo (tema azul, pero “apagado”)
      'rechazada' => 'inline-flex items-center gap-2 rounded-full bg-slate-500/10 text-slate-200 border border-slate-400/20 px-3 py-1 text-[11px] font-semibold',
      default     => 'inline-flex items-center gap-2 rounded-full bg-white/5 text-zinc-200 border border-white/10 px-3 py-1 text-[11px] font-semibold',
    };
  };

  $dot = function($estado) {
    return match($estado) {
      'pendiente' => 'h-2 w-2 rounded-full bg-sky-300',
      'aprobada'  => 'h-2 w-2 rounded-full bg-indigo-300',
      'rechazada' => 'h-2 w-2 rounded-full bg-slate-300',
      default     => 'h-2 w-2 rounded-full bg-zinc-400',
    };
  };

  $chip = 'inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-sky-100 hover:bg-white/10 hover:border-sky-400/20';
@endphp

<div class="space-y-6">
  {{-- Alerts --}}
  @if (session('status'))
    <div class="rounded-2xl border border-sky-500/40 bg-sky-500/10 px-4 py-3 text-sm text-sky-200">
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

  {{-- Header / Controls --}}
  <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-indigo-600/15 via-zinc-900/75 to-zinc-900/70 p-6 shadow-2xl ring-1 ring-indigo-500/10">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200/70">KYC</p>
        <h2 class="mt-1 text-2xl font-bold text-white">Gestión de verificaciones</h2>
        <p class="mt-1 text-sm text-zinc-400">Aprueba o rechaza solicitudes con documentos adjuntos.</p>
      </div>

      <form method="GET" action="{{ route('admin.verificaciones') }}" class="flex flex-wrap items-end gap-2">
        <div>
          <label class="text-xs text-zinc-400">Estado</label>
          <select name="estado"
                  class="mt-1 rounded-xl border border-white/15 bg-zinc-950/60 px-3 py-2 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
            <option value="">Todos</option>
            <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
            <option value="aprobada" @selected($estado === 'aprobada')>Aprobada</option>
            <option value="rechazada" @selected($estado === 'rechazada')>Rechazada</option>
          </select>
        </div>

        <button type="submit" class="{{ $btnPrimary }}">Filtrar</button>

        @if($estado)
          <a href="{{ route('admin.verificaciones') }}" class="{{ $btnGhost }}">Limpiar</a>
        @endif
      </form>
    </div>
  </section>

  {{-- List --}}
  <section class="space-y-4">
    @forelse ($solicitudes as $solicitud)
      @php
        $adj = $solicitud->adjuntos ?? [];
        $nombre = $solicitud->user->nombre_completo ?? $solicitud->user->name;
        $email = $solicitud->user->email;
      @endphp

      <article class="rounded-3xl border border-white/10 bg-zinc-900/70 shadow-xl ring-1 ring-indigo-500/10 overflow-hidden">
        <div class="p-6">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            {{-- Left --}}
            <div class="min-w-0 space-y-3">
              <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0">
                  <p class="text-base font-semibold text-white truncate">{{ $nombre }}</p>
                  <p class="text-xs text-zinc-400 truncate">{{ $email }}</p>
                </div>

                <span class="{{ $badge($solicitud->estado) }}">
                  <span class="{{ $dot($solicitud->estado) }}"></span>
                  {{ strtoupper($solicitud->estado) }}
                </span>

                <span class="text-xs text-zinc-500">
                  ID: <span class="text-zinc-300 font-semibold">{{ $solicitud->id }}</span>
                </span>
              </div>

              @if ($solicitud->nota)
                <div class="rounded-2xl border border-sky-500/15 bg-sky-500/5 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-200/70">Nota</p>
                  <p class="mt-2 text-sm text-zinc-200 leading-relaxed">{{ $solicitud->nota }}</p>
                </div>
              @endif

              {{-- Adjuntos --}}
              <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500">Adjuntos</p>
                <div class="flex flex-wrap gap-2">
                  @if (!empty($adj['documento_frontal']))
                    <a target="_blank"
                       href="{{ route('admin.verificaciones.adjunto', [$solicitud, 'documento_frontal']) }}"
                       class="{{ $chip }}">
                      <span class="h-2 w-2 rounded-full bg-sky-300"></span> Documento frontal
                    </a>
                  @endif

                  @if (!empty($adj['documento_reverso']))
                    <a target="_blank"
                       href="{{ route('admin.verificaciones.adjunto', [$solicitud, 'documento_reverso']) }}"
                       class="{{ $chip }}">
                      <span class="h-2 w-2 rounded-full bg-sky-300"></span> Documento reverso
                    </a>
                  @endif

                  @if (!empty($adj['selfie']))
                    <a target="_blank"
                       href="{{ route('admin.verificaciones.adjunto', [$solicitud, 'selfie']) }}"
                       class="{{ $chip }}">
                      <span class="h-2 w-2 rounded-full bg-sky-300"></span> Selfie
                    </a>
                  @endif

                  @if (empty($adj) || (empty($adj['documento_frontal']) && empty($adj['documento_reverso']) && empty($adj['selfie'])))
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-zinc-400">
                      <span class="h-2 w-2 rounded-full bg-zinc-500"></span> Archivos no disponibles
                    </span>
                  @endif
                </div>
              </div>
            </div>

            {{-- Right: acciones --}}
            <div class="w-full lg:w-[420px]">
              @if ($solicitud->estado === 'pendiente')
                <form method="POST"
                      action="{{ route('admin.verificaciones.update', $solicitud) }}"
                      class="rounded-3xl border border-white/10 bg-zinc-950/60 p-5 shadow-inner ring-1 ring-indigo-500/10 space-y-4">
                  @csrf
                  @method('PATCH')

                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-200/70">Decisión</p>
                      <p class="mt-1 text-sm text-zinc-300">Revisa adjuntos y decide.</p>
                    </div>

                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-500/10 text-sky-200 border border-sky-500/25 px-3 py-1 text-[11px] font-semibold">
                      <span class="h-2 w-2 rounded-full bg-sky-300"></span> Pendiente
                    </span>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-400">Nota (opcional)</label>
                    <textarea name="nota"
                              rows="3"
                              class="{{ $input }}"
                              placeholder="Ej: Documento borroso, subir nuevamente.">{{ old('nota') }}</textarea>
                  </div>

                  <div class="grid grid-cols-2 gap-2">
                    <button name="accion" value="aprobar" class="{{ $btnApprove }}">Aprobar</button>
                    <button name="accion" value="rechazar" class="{{ $btnReject }}">Rechazar</button>
                  </div>

                  <p class="text-[11px] text-zinc-500 leading-relaxed">
                    Tip: si rechazas, deja una nota clara para que el usuario sepa cómo corregir.
                  </p>
                </form>
              @else
                <div class="rounded-3xl border border-white/10 bg-zinc-950/50 p-5">
                  <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-200/70">Estado</p>
                  <div class="mt-2 flex items-center justify-between">
                    <span class="{{ $badge($solicitud->estado) }}">
                      <span class="{{ $dot($solicitud->estado) }}"></span>
                      {{ strtoupper($solicitud->estado) }}
                    </span>
                    <span class="text-xs text-zinc-500">Solicitud cerrada</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </article>
    @empty
      <div class="rounded-3xl border border-white/10 bg-zinc-900/70 p-10 text-center shadow-xl">
        <p class="text-sm text-zinc-400">No hay solicitudes con este filtro.</p>
      </div>
    @endforelse
  </section>

  {{-- Pagination --}}
  <div class="rounded-3xl border border-white/10 bg-zinc-900/60 px-6 py-4 text-right text-xs text-zinc-400 shadow-xl">
    {{ $solicitudes->links() }}
  </div>
</div>
@endsection
