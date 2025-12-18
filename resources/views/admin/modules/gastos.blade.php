@extends('admin.layouts.panel')

@section('title', 'Gastos y comprobantes')
@section('active', 'auditorias')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Str;
    @endphp
    @php
        $btnSolid = 'inline-flex items-center gap-2 rounded-xl bg-[#4f46e5] px-4 py-2.5 text-sm font-semibold text-white border border-[#4f46e5] hover:bg-[#4338ca]';
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-white/10 bg-zinc-900/75 shadow-2xl ring-1 ring-indigo-500/10 admin-accent-card">
            <div class="border-b border-white/5 px-6 py-5 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-zinc-400">Auditoría de gastos</p>
                <h1 class="text-2xl font-bold text-white">Comprobantes y estados</h1>
                <p class="text-sm text-zinc-400">Filtra por estado de auditoría, proveedor o concepto.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-4 p-6 text-sm text-white">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Total gastos</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Con comprobante</p>
                    <p class="text-2xl font-bold text-emerald-200">{{ $stats['conAdjuntos'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Pendientes</p>
                    <p class="text-2xl font-bold text-amber-200">{{ $stats['pendientes'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-zinc-400">Observados/Rechazados</p>
                    <p class="text-2xl font-bold text-rose-200">{{ $stats['observados'] }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.gastos') }}" class="grid gap-3 md:grid-cols-[2fr,1fr,auto] md:items-end px-6 pb-6">
                <div>
                    <label class="text-xs text-zinc-400">Búsqueda</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Concepto, proveedor o proyecto"
                           class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-zinc-500 focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Estado auditoría</label>
                    <select name="estado_auditoria" class="mt-1 w-full appearance-none rounded-xl border border-white/15 bg-zinc-900/80 px-4 py-2.5 text-sm text-white focus:border-white/40 focus:ring-white/20">
                        <option value="">Todos</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado }}" @selected($estadoAuditoria === $estado)>{{ ucfirst($estado) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="{{ $btnSolid }}">Filtrar</button>
                    <a href="{{ route('admin.gastos') }}" class="admin-btn admin-btn-ghost">Limpiar</a>
                </div>
            </form>

            <div class="px-6 pb-6 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto rounded-2xl border border-white/10 bg-white/5">
                    <table class="min-w-full divide-y divide-white/10 text-sm">
                        <thead class="bg-white/5 text-left text-xs uppercase tracking-[0.2em] text-zinc-400">
                            <tr>
                                <th class="px-4 py-3">Proyecto / Solicitud</th>
                                <th class="px-4 py-3">Proveedor</th>
                                <th class="px-4 py-3">Concepto</th>
                                <th class="px-4 py-3">Monto</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Auditoría</th>
                                <th class="px-4 py-3">Adjuntos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse ($pagos as $pago)
                                @php
                                    $estado = $pago->estado_auditoria ?? 'pendiente';
                                    $estadoClass = match ($estado) {
                                        'aprobado' => 'bg-emerald-500/15 text-emerald-100 border-emerald-400/40',
                                        'rechazado', 'observado' => 'bg-rose-500/15 text-rose-100 border-rose-400/40',
                                        default => 'bg-white/10 text-zinc-200 border-white/15',
                                    };
                                    $adjuntos = collect($pago->adjuntos ?? [])
                                        ->filter()
                                        ->map(function ($path) {
                                            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                                                return ['label' => basename($path), 'url' => $path];
                                            }
                                            $normalized = ltrim(preg_replace('/^public\\//', '', $path), '/');
                                            if (Storage::disk('public')->exists($normalized)) {
                                                return ['label' => basename($normalized), 'url' => Storage::url($normalized)];
                                            }
                                            if (file_exists(public_path('storage/' . $normalized))) {
                                                return ['label' => basename($normalized), 'url' => asset('storage/' . $normalized)];
                                            }
                                            if (file_exists(public_path($path))) {
                                                return ['label' => basename($path), 'url' => asset($path)];
                                            }
                                            return ['label' => basename($path), 'url' => Storage::url($path)];
                                        });
                                @endphp
                                <tr class="text-zinc-200 align-top">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $pago->solicitud->proyecto->titulo ?? 'Proyecto' }}</p>
                                        <p class="text-xs text-zinc-400">Solicitud: {{ $pago->solicitud->hito ?? 'N/D' }}</p>
                                        <p class="text-[11px] text-zinc-500">Estado solicitud: {{ ucfirst($pago->solicitud->estado ?? 'pendiente') }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $pago->proveedor->nombre_proveedor ?? 'Proveedor' }}</p>
                                        @if($pago->proveedor?->especialidad)
                                            <p class="text-xs text-zinc-400">{{ $pago->proveedor->especialidad }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm">{{ $pago->concepto ?? 'Sin concepto' }}</p>
                                        @if($pago->nota_auditoria)
                                            <p class="mt-1 text-[11px] text-amber-200">Nota auditor: {{ $pago->nota_auditoria }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 tabular-nums">US$ {{ number_format($pago->monto, 2) }}</td>
                                    <td class="px-4 py-3">{{ $pago->fecha_pago?->format('Y-m-d') ?? 'N/D' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold {{ $estadoClass }}">
                                            {{ ucfirst($estado) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($adjuntos->isEmpty())
                                            <span class="text-xs text-zinc-500">Sin archivos</span>
                                        @else
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($adjuntos as $index => $file)
                                                    <a href="{{ $file['url'] }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-indigo-400/40 bg-indigo-500/10 px-3 py-1 text-[11px] font-semibold text-indigo-100 hover:border-indigo-300/70">
                                                        Archivo {{ $index + 1 }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-sm text-zinc-400">No hay gastos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right text-xs text-zinc-400">
                    {{ $pagos->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection
