@extends('admin.layouts.panel')

@section('title', 'Proveedores')
@section('active', 'proveedores')

@section('content')
    @php
        $btnSolid = 'inline-flex items-center gap-2 rounded-xl bg-[#4f46e5] px-4 py-2.5 text-sm font-semibold text-white border border-[#4f46e5] hover:bg-[#4338ca]';
    @endphp

    <div class="space-y-8">
        <section class="rounded-3xl border border-white/10 bg-zinc-900/75 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="border-b border-white/5 px-6 py-6 space-y-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Control de proveedores</p>
                        <h1 class="text-2xl font-bold text-white">Directorio global de proveedores</h1>
                        <p class="text-sm text-zinc-400 max-w-2xl">
                            Filtra por proyecto o creador para auditar contrataciones, especialidades y calificaciones
                            de los proveedores registrados en la plataforma.
                        </p>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:w-auto">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Proveedores</p>
                            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Con proyecto</p>
                            <p class="text-2xl font-bold text-white">{{ number_format($stats['conProyecto']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-[0.3em] text-zinc-500">Calificacion promedio</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $stats['calificacionPromedio'] > 0 ? number_format($stats['calificacionPromedio'], 2) . ' / 5' : 'N/D' }}
                            </p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.proveedores') }}" class="grid gap-3 sm:grid-cols-[2fr,auto] sm:items-end">
                    <div>
                        <label class="text-xs text-zinc-400">Busqueda</label>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Nombre, especialidad o contacto"
                               class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-zinc-500 focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="{{ $btnSolid }}">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.proveedores') }}" class="admin-btn admin-btn-ghost">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="p-6 space-y-4">
                @forelse ($proveedores as $proveedor)
                    @php
                        $calificacion = $proveedor->calificacion_promedio ? round($proveedor->calificacion_promedio, 1) : null;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner shadow-black/30">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold text-white">{{ $proveedor->nombre_proveedor }}</h3>
                                <p class="text-sm text-zinc-400">Especialidad: {{ $proveedor->especialidad ?? 'Sin especialidad' }}</p>
                                @if ($proveedor->info_contacto)
                                    <p class="text-xs text-zinc-500">Contacto: {{ $proveedor->info_contacto }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-100 border border-indigo-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 .587l3.668 7.429L24 9.748l-6 5.844L19.335 24 12 19.897 4.665 24 6 15.592 0 9.748l8.332-1.732z"/>
                                    </svg>
                                    {{ $calificacion ? $calificacion . ' / 5' : 'Sin calificacion' }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-100 border border-emerald-500/30">
                                    Proyecto: {{ $proveedor->proyecto->titulo ?? 'No asignado' }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-100 border border-amber-500/30">
                                    Creador: {{ $proveedor->creador->nombre_completo ?? $proveedor->creador->name ?? 'N/D' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                            <span class="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-black/30 px-3 py-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Registrado: {{ optional($proveedor->created_at)->format('Y-m-d') ?? 'N/D' }}
                            </span>
                            @if ($proveedor->proyecto)
                                <a href="{{ route('admin.proyectos.show', $proveedor->proyecto) }}" class="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-3 py-1 text-indigo-200 hover:text-white">
                                    Ver proyecto
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @endif
                            @if ($proveedor->creador)
                                <a href="{{ route('admin.users.show', $proveedor->creador) }}" class="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-3 py-1 text-indigo-200 hover:text-white">
                                    Ver creador
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @endif
                            <a href="{{ route('admin.proveedores.show', $proveedor) }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-indigo-100 hover:text-white">
                                Ver proveedor
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="py-10 text-center text-sm text-zinc-400">Aun no hay proveedores registrados.</p>
                @endforelse

                <div class="border-t border-white/5 px-2 pt-4 text-right text-xs text-zinc-400">
                    {{ $proveedores->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection
