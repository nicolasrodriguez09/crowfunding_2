@extends('creator.layouts.panel')

@section('title', 'Nuevo proyecto')
@section('active', 'proyectos')
@section('back_url', route('creador.proyectos'))
@section('back_label', 'Volver a proyectos')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 space-y-6">
        <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-emerald-600/25 via-zinc-900/70 to-zinc-900/70 p-8 shadow-2xl">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-300">Publicar</p>
                    <h2 class="text-2xl font-bold text-white">Crea un nuevo proyecto</h2>
                    <p class="text-sm text-zinc-300">Define la informacion base: meta, modelo, ubicacion, fechas y portada.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-4 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mt-4 rounded-2xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="proyecto-form" method="POST" action="{{ route('creador.proyectos.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label class="text-sm text-zinc-300">Titulo *</label>
                    <input name="titulo" value="{{ old('titulo') }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Nombre de tu proyecto">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm text-zinc-300">Descripcion *</label>
                    <textarea name="descripcion_proyecto" rows="3" required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Describe el problema, la solucion y el impacto">{{ old('descripcion_proyecto') }}</textarea>
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Meta de financiacion (USD) *</label>
                    <input type="number" step="0.01" min="1" name="meta_financiacion" value="{{ old('meta_financiacion') }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="10000">
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Modelo de financiamiento *</label>
                    <select name="modelo_financiamiento_id" required class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-900/80 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">Selecciona un modelo</option>
                        @foreach ($modelos as $modelo)
                            <option value="{{ $modelo->id }}" @selected(old('modelo_financiamiento_id') == $modelo->id)>{{ $modelo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Categoria *</label>
                    <select name="categoria_id" required class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-900/80 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">Selecciona una categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Ubicacion *</label>
                    <input name="ubicacion_geografica" value="{{ old('ubicacion_geografica') }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ciudad, pais">
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Fecha limite *</label>
                    <input type="date" name="fecha_limite" value="{{ old('fecha_limite') }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div class="md:col-span-2 space-y-3 rounded-2xl border border-white/10 bg-black/40 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">Cronograma de hitos</p>
                            <p class="text-sm text-zinc-300">Define metas, fechas estimadas y montos.</p>
                        </div>
                        <button type="button" id="cronograma-add" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500">
                            Añadir hito
                        </button>
                    </div>
                    <div class="grid gap-3 md:grid-cols-[1.2fr,1fr,1fr]">
                        <div>
                            <label class="text-xs text-zinc-400">Hito / meta</label>
                            <input id="cronograma-titulo" type="text" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Ej. Prototipo funcional">
                        </div>
                        <div>
                            <label class="text-xs text-zinc-400">Fecha estimada</label>
                            <input id="cronograma-fecha" type="date" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="text-xs text-zinc-400">Monto estimado (USD)</label>
                            <input id="cronograma-monto" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="1500">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400">Descripción breve</label>
                        <textarea id="cronograma-descripcion" rows="2" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400" placeholder="Entregables, alcance, dependencias"></textarea>
                    </div>
                    <input type="hidden" name="cronograma" id="cronograma-input" value="{{ old('cronograma') }}" required>
                    @error('cronograma')
                        <p class="text-xs text-red-300">{{ $message }}</p>
                    @enderror
                    <div id="cronograma-lista" class="space-y-2 text-sm text-zinc-200">
                        <p class="text-xs text-zinc-500">Aún no hay hitos agregados.</p>
                    </div>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-sm text-zinc-300">Imagen de portada</label>
                    <label for="portada" class="group flex flex-col gap-2 rounded-xl border-2 border-dashed border-emerald-400/40 bg-emerald-500/5 px-4 py-6 text-sm text-emerald-50 cursor-pointer hover:border-emerald-400 transition">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-100 border border-emerald-500/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-semibold text-white">Arrastra o haz clic para seleccionar</p>
                                <p id="portada-name" class="text-[12px] text-emerald-200/80">Sin archivo seleccionado</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-emerald-200/70">JPG, PNG o WEBP. Máx. 8MB.</p>
                        <input id="portada" type="file" name="portada" accept="image/*" class="hidden" required>
                    </label>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500">
                        Publicar en borrador
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('portada');
    const label = document.getElementById('portada-name');
    if (input && label) {
        input.addEventListener('change', () => {
            if (input.files && input.files.length) {
                label.textContent = input.files[0].name;
                label.classList.remove('text-emerald-200/80');
                label.classList.add('text-white');
                label.closest('label')?.classList.add('border-emerald-400');
            } else {
                label.textContent = 'Sin archivo seleccionado';
                label.classList.add('text-emerald-200/80');
                label.classList.remove('text-white');
                label.closest('label')?.classList.remove('border-emerald-400');
            }
        });
    }

    // Cronograma de hitos (UI local)
    const cronogramaInput = document.getElementById('cronograma-input');
    const list = document.getElementById('cronograma-lista');
    const btn = document.getElementById('cronograma-add');
    const fTitulo = document.getElementById('cronograma-titulo');
    const fFecha = document.getElementById('cronograma-fecha');
    const fMonto = document.getElementById('cronograma-monto');
    const fDesc = document.getElementById('cronograma-descripcion');

    let cronograma = [];
    let counter = 0;
    try {
        if (cronogramaInput?.value) {
            const parsed = JSON.parse(cronogramaInput.value);
            if (Array.isArray(parsed)) cronograma = parsed;
        }
    } catch (_) {}

    const renderCronograma = () => {
        if (!list) return;
        list.innerHTML = '';
        if (!cronograma.length) {
            list.innerHTML = '<p class="text-xs text-zinc-500">Aún no hay hitos agregados.</p>';
        } else {
            cronograma.forEach((h, idx) => {
                const row = document.createElement('div');
                row.className = 'flex items-start justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm';
                row.innerHTML = `
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center rounded-full bg-emerald-500/15 text-emerald-200 border border-emerald-400/30 px-2 py-0.5 text-[11px] font-semibold">
                                Hito ${h.numero ?? idx + 1}
                            </span>
                            <p class="font-semibold text-white">${h.titulo || 'Hito'}</p>
                        </div>
                        <p class="text-[12px] text-zinc-400">${h.fecha || 'Fecha estimada'} · USD ${Number(h.monto || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
                        ${h.descripcion ? `<p class="text-xs text-zinc-300">${h.descripcion}</p>` : ''}
                    </div>
                    <button type="button" data-idx="${idx}" class="text-[12px] text-rose-300 hover:text-white">Eliminar</button>
                `;
                list.appendChild(row);
            });
        }
        if (cronogramaInput) cronogramaInput.value = JSON.stringify(cronograma);
    };

    if (btn) {
        btn.addEventListener('click', () => {
            const titulo = fTitulo?.value?.trim();
            const fecha = fFecha?.value;
            const monto = fMonto?.value;
            const descripcion = fDesc?.value?.trim();
            if (!titulo || !fecha || !monto) {
                alert('Completa título, fecha y monto antes de agregar el hito.');
                return;
            }
            counter = cronograma.length ? Math.max(counter, cronograma.length) : counter;
            counter += 1;
            cronograma.push({
                numero: counter,
                titulo: titulo || `Hito ${counter}`,
                fecha: fecha || null,
                monto: monto ? parseFloat(monto) : 0,
                descripcion: descripcion || null,
            });
            if (fTitulo) fTitulo.value = '';
            if (fFecha) fFecha.value = '';
            if (fMonto) fMonto.value = '';
            if (fDesc) fDesc.value = '';
            renderCronograma();
        });
    }

    if (list) {
        list.addEventListener('click', (e) => {
            const target = e.target;
            if (target && target.dataset && target.dataset.idx !== undefined) {
                const i = Number(target.dataset.idx);
                cronograma.splice(i, 1);
                renderCronograma();
            }
        });
    }

    renderCronograma();

    // Sincroniza antes de enviar el formulario
    const form = document.getElementById('proyecto-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!cronograma.length) {
                e.preventDefault();
                alert('Agrega al menos un hito al cronograma antes de publicar.');
                return false;
            }
            if (cronogramaInput) cronogramaInput.value = JSON.stringify(cronograma);
        });
    }
});
</script>
@endpush


