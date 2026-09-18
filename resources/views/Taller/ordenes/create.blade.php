@extends('taller.layouts.app')

@section('title', 'Nueva Orden de Trabajo - MecxiHub')

@section('content')

    <div class="mb-6">
        <a href="{{ route('taller.ordenes') }}" class="text-sm text-gray-500 hover:text-brand-blue transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a órdenes
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-2 flex items-center gap-3">
            <span
                class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-orange to-orange-600 text-white flex items-center justify-center shadow-lg shadow-orange-500/30">
                <i class="fa-solid fa-plus text-lg"></i>
            </span>
            Nueva Orden de Trabajo
        </h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <span>Corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('taller.ordenes.store') }}" method="POST" id="orden-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Columna principal --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Cliente --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user text-brand-blue"></i> Datos del Cliente
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nombre
                                completo *</label>
                            <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Email</label>
                            <input type="email" name="cliente_email" value="{{ old('cliente_email') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Teléfono</label>
                            <input type="number" name="cliente_telefono" value="{{ old('cliente_telefono') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                    </div>
                </div>

                {{-- Vehículo --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-car text-brand-blue"></i> Vehículo
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Marca
                                *</label>
                            <input type="text" name="vehiculo[marca]" value="{{ old('vehiculo.marca') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Modelo
                                *</label>
                            <input type="text" name="vehiculo[modelo]" value="{{ old('vehiculo.modelo') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Año
                                *</label>
                            <input type="number" name="vehiculo[anio]" min="1900" max="{{ date('Y') + 1 }}"
                                value="{{ old('vehiculo.anio', date('Y')) }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Color</label>
                            <input type="text" name="vehiculo[color]" value="{{ old('vehiculo.color') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Placas
                                *</label>
                            <input type="text" name="vehiculo[placas]" value="{{ old('vehiculo.placas') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm uppercase">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Kilometraje</label>
                            <input type="number" name="vehiculo[kilometraje]" min="0"
                                value="{{ old('vehiculo.kilometraje', 0) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                    </div>
                </div>

                {{-- Fallas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">

                    {{-- Selector de modo de asignación --}}
                    <div class="mb-5 p-4 bg-purple-50/60 border border-purple-200 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-user-gear text-purple-600"></i>
                            <h4 class="text-sm font-bold text-purple-800 uppercase tracking-wider">Asignación de
                                responsables</h4>
                            <span
                                class="text-[10px] bg-purple-200 text-purple-800 px-2 py-0.5 rounded-full font-bold">INTERNO</span>
                        </div>

                        @if (empty($personal))
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                No tienes personal activo. <a href="{{ route('taller.personal') }}"
                                    class="underline font-semibold">Agrega personal</a> para poder asignar responsables.
                            </p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="modo_asignacion" value="none"
                                        class="peer sr-only modo-asignacion" checked>
                                    <div
                                        class="px-3 py-2.5 rounded-lg border-2 border-gray-200 hover:border-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-100/50 transition text-center">
                                        <i class="fa-solid fa-ban text-gray-400 mr-1"></i>
                                        <span class="text-xs font-semibold">Sin asignar</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="modo_asignacion" value="general"
                                        class="peer sr-only modo-asignacion">
                                    <div
                                        class="px-3 py-2.5 rounded-lg border-2 border-gray-200 hover:border-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-100/50 transition text-center">
                                        <i class="fa-solid fa-user-check text-purple-500 mr-1"></i>
                                        <span class="text-xs font-semibold">Responsable general</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="modo_asignacion" value="por_falla"
                                        class="peer sr-only modo-asignacion">
                                    <div
                                        class="px-3 py-2.5 rounded-lg border-2 border-gray-200 hover:border-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-100/50 transition text-center">
                                        <i class="fa-solid fa-users-gear text-purple-500 mr-1"></i>
                                        <span class="text-xs font-semibold">Por falla</span>
                                    </div>
                                </label>
                            </div>

                            {{-- Selector de responsable general --}}
                            <div id="selector-responsable-general" class="mt-3 hidden">
                                <label class="block text-xs font-semibold text-purple-800 uppercase tracking-wider mb-1">
                                    Responsable general del vehículo
                                </label>
                                <select name="responsable_general_id" id="responsable_general_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-purple-200 focus:outline-none focus:border-purple-500 text-sm bg-white">
                                    <option value="">— Seleccionar —</option>
                                    @foreach ($personal as $p)
                                        <option value="{{ $p['id'] }}" data-nombre="{{ $p['nombre'] }}">
                                            {{ $p['nombre'] }}{{ !empty($p['puesto']) ? ' · ' . $p['puesto'] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="responsable_general_nombre" id="responsable_general_nombre">
                            </div>

                            <p id="hint-asignacion" class="text-[11px] text-purple-700 mt-2 hidden">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                <span id="hint-texto"></span>
                            </p>
                        @endif
                    </div>

                    {{-- Fotos del vehículo (se suben al crear) --}}
                    <div class="mb-5 p-4 bg-blue-50/60 border border-blue-200 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-camera text-brand-blue"></i>
                            <h4 class="text-sm font-bold text-blue-800 uppercase tracking-wider">Fotos del vehículo</h4>
                            <span
                                class="text-[10px] bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full font-bold">OPCIONAL</span>
                        </div>
                        <p class="text-[11px] text-blue-700 mb-3">
                            Adjunta fotos ahora y se subirán automáticamente al crear la orden.
                        </p>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider">
                                <i class="fa-solid fa-car-side mr-1"></i> Fotos generales
                            </span>
                            <label
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-blue-100 text-brand-blue rounded-lg font-semibold text-[11px] transition border border-blue-300">
                                <i class="fa-solid fa-plus"></i> Añadir
                                <input type="file" id="input-evidencia-vehiculo-create" class="hidden"
                                    accept="image/*" multiple>
                            </label>
                        </div>
                        <div id="galeria-vehiculo-create"
                            class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 min-h-[40px]"></div>
                    </div>

                    {{-- Encabezado de fallas + botón --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-screwdriver-wrench text-brand-orange"></i> Fallas Detectadas
                        </h3>
                        <button type="button" id="btn-add-falla"
                            class="px-3 py-1.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-plus mr-1"></i> Agregar falla
                        </button>
                    </div>

                    <div id="fallas-container" class="space-y-4"></div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total sugerido (sumatoria de precios):</span>
                        <span id="total-sugerido" class="text-xl font-bold text-brand-orange">$0.00</span>
                    </div>
                </div>

            </div>

            {{-- Columna lateral --}}
            <div class="space-y-6">

                {{-- Fechas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-brand-blue"></i> Fechas
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Fecha de
                                orden</label>
                            <div
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-600 flex items-center gap-2">
                                <i class="fa-solid fa-lock text-gray-400 text-xs"></i>
                                {{ now()->format('d/m/Y H:i') }}
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Generada automáticamente por el sistema.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Entrega
                                estimada *</label>
                            <input type="date" id="fecha_entrega_estimada" name="fecha_entrega_estimada"
                                value="{{ old('fecha_entrega_estimada', now()->addDay()->format('Y-m-d')) }}"
                                min="{{ now()->format('Y-m-d') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                            <p class="text-[11px] text-gray-400 mt-1">No puede ser anterior a hoy.</p>
                        </div>
                    </div>
                </div>

                {{-- Presupuesto --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-dollar-sign text-green-600"></i> Presupuesto
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Anticipo</label>
                            <input type="number" step="0.01" min="0" name="presupuesto[anticipo]"
                                value="{{ old('presupuesto.anticipo', 0) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Método
                                de pago</label>
                            <select name="presupuesto[metodo_pago]"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                                <option value="">Seleccionar...</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="pt-3 border-t border-gray-100">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Precio final *
                            </label>
                            <input type="number" step="0.01" min="0" name="precio_final"
                                id="precio_final_input" value="{{ old('precio_final', 0) }}"
                                class="w-full px-4 py-2.5 rounded-xl border-2 border-brand-orange focus:outline-none focus:border-brand-orange text-base font-bold text-gray-900">
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-[11px] text-gray-400">Se autocompleta con el total sugerido, pero puedes
                                    editarlo.</p>
                                <button type="button" id="btn-restaurar-total"
                                    class="text-[11px] text-brand-blue hover:underline font-semibold">
                                    Restaurar total sugerido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col gap-2">
                    <button type="submit"
                        class="w-full px-6 py-3 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Crear Orden
                    </button>
                    <a href="{{ route('taller.ordenes') }}"
                        class="w-full text-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                        Cancelar
                    </a>
                </div>

            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            window.PERSONAL_TALLER = @json($personal ?? []);
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('fallas-container');
                const totalEl = document.getElementById('total-sugerido');
                const precioFinalInput = document.getElementById('precio_final_input');
                const btnRestaurar = document.getElementById('btn-restaurar-total');

                const personalTaller = window.PERSONAL_TALLER || [];

                let precioFinalTocado = false;
                let fallaIndex = 0;
                let modoActual = 'none';

                // ---------- Radios de asignación ----------
                const radiosModo = document.querySelectorAll('.modo-asignacion');
                const selectorGeneral = document.getElementById('selector-responsable-general');
                const inputGeneralNombre = document.getElementById('responsable_general_nombre');
                const selectGeneral = document.getElementById('responsable_general_id');
                const hintAsignacion = document.getElementById('hint-asignacion');
                const hintTexto = document.getElementById('hint-texto');

                // ---------- Cálculo ----------
                function recalcularTotal() {
                    let total = 0;
                    container.querySelectorAll('.falla-precio').forEach(inp => {
                        total += parseFloat(inp.value) || 0;
                    });
                    totalEl.textContent = '$' + total.toFixed(2);
                    if (!precioFinalTocado && precioFinalInput) {
                        precioFinalInput.value = total.toFixed(2);
                    }
                    return total;
                }

                precioFinalInput?.addEventListener('input', () => {
                    precioFinalTocado = true;
                });
                btnRestaurar?.addEventListener('click', function() {
                    precioFinalTocado = false;
                    recalcularTotal();
                });

                // ---------- Modo de asignación ----------
                function aplicarModo(modo) {
                    modoActual = modo;

                    if (modo === 'general') {
                        selectorGeneral?.classList.remove('hidden');
                    } else {
                        selectorGeneral?.classList.add('hidden');
                        if (selectGeneral) selectGeneral.value = '';
                        if (inputGeneralNombre) inputGeneralNombre.value = '';
                    }

                    // Re-renderizar las fallas para mostrar/ocultar el select por falla
                    const fallasSnapshot = [];
                    container.querySelectorAll('.falla-item').forEach(item => {
                        fallasSnapshot.push({
                            categoria: item.querySelector('[name*="[categoria]"]')?.value || '',
                            prioridad: item.querySelector('[name*="[prioridad]"]')?.value || 'Media',
                            precio: item.querySelector('[name*="[precio]"]')?.value || 0,
                            descripcion: item.querySelector('[name*="[descripcion]"]')?.value || '',
                            responsable_id: item.querySelector('[name*="[responsable_id]"]')?.value ||
                                '',
                            responsable_nombre: item.querySelector('[name*="[responsable_nombre]"]')
                                ?.value || '',
                        });
                    });
                    container.innerHTML = '';
                    fallasSnapshot.forEach(f => container.appendChild(createFallaRow(f)));

                    // Hint
                    if (hintAsignacion && hintTexto) {
                        if (modo === 'general') {
                            hintTexto.textContent = 'Un solo responsable se hará cargo de todo el vehículo.';
                            hintAsignacion.classList.remove('hidden');
                        } else if (modo === 'por_falla') {
                            hintTexto.textContent = 'Asigna un responsable distinto a cada falla.';
                            hintAsignacion.classList.remove('hidden');
                        } else {
                            hintAsignacion.classList.add('hidden');
                        }
                    }

                    recalcularTotal();
                }

                radiosModo.forEach(r => {
                    r.addEventListener('change', function() {
                        if (this.checked) aplicarModo(this.value);
                    });
                });

                selectGeneral?.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (inputGeneralNombre) {
                        inputGeneralNombre.value = opt.dataset.nombre || '';
                    }
                });

                // ---------- Fila de falla ----------
                function createFallaRow(data = {}) {
                    const idx = fallaIndex++;
                    const mostrarResponsable = (modoActual === 'por_falla');

                    const div = document.createElement('div');
                    div.className = 'falla-item bg-gray-50 rounded-xl p-4 border border-gray-200';
                    div.innerHTML = `
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fa-solid fa-wrench text-brand-orange mr-1"></i> Falla
                        </span>
                        <button type="button" class="btn-remove-falla w-7 h-7 rounded-lg bg-white hover:bg-red-100 hover:text-red-600 text-gray-500 flex items-center justify-center transition border border-gray-200">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Categoría *</label>
                            <input type="text" name="fallas[${idx}][categoria]" value="${data.categoria || ''}" required
                                placeholder="Ej: Motor, Frenos..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Prioridad *</label>
                            <select name="fallas[${idx}][prioridad]" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                                <option value="Baja" ${data.prioridad === 'Baja' ? 'selected' : ''}>Baja</option>
                                <option value="Media" ${data.prioridad === 'Media' || !data.prioridad ? 'selected' : ''}>Media</option>
                                <option value="Alta" ${data.prioridad === 'Alta' ? 'selected' : ''}>Alta</option>
                                <option value="Urgente" ${data.prioridad === 'Urgente' ? 'selected' : ''}>Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Precio *</label>
                            <input type="number" step="0.01" min="0" name="fallas[${idx}][precio]" value="${data.precio ?? 0}" required
                                class="falla-precio w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Descripción</label>
                            <textarea name="fallas[${idx}][descripcion]" rows="2"
                                placeholder="Describe el problema detectado (opcional)..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">${data.descripcion || ''}</textarea>
                        </div>
                        ${mostrarResponsable ? `
                                                    <div class="md:col-span-3 pt-3 border-t border-purple-100">
                                                        <label class="block text-[10px] font-semibold text-purple-700 uppercase tracking-wider mb-1">
                                                            <i class="fa-solid fa-user-gear mr-1"></i> Responsable de esta falla
                                                        </label>
                                                        <select name="fallas[${idx}][responsable_id]" class="select-responsable-falla w-full px-3 py-2 rounded-lg border border-purple-200 focus:outline-none focus:border-purple-500 text-sm bg-white">
                                                            <option value="">— Seleccionar responsable —</option>
                                                            ${personalTaller.map(p => `
                                        <option value="${p.id}" data-nombre="${p.nombre}" ${data.responsable_id === p.id ? 'selected' : ''}>
                                            ${p.nombre}${p.puesto ? ' · ' + p.puesto : ''}
                                        </option>
                                    `).join('')}
                                                        </select>
                                                        <input type="hidden" name="fallas[${idx}][responsable_nombre]" value="${data.responsable_nombre || ''}" class="input-responsable-nombre">
                                                    </div>
                                                ` : ''}

                        ${/* Evidencias por falla */ ''}
                        <div class="md:col-span-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fa-solid fa-camera text-brand-blue mr-1"></i> Evidencias fotográficas
                                </label>
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-brand-blue rounded-lg font-semibold text-[10px] transition border border-blue-200">
                                    <i class="fa-solid fa-plus"></i> Añadir
                                    <input type="file" class="hidden input-evidencia-falla-create" data-falla-idx="${idx}" accept="image/*" multiple>
                                </label>
                            </div>
                            <div class="evidencias-falla-create grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 min-h-[40px]" data-falla-idx="${idx}"></div>
                        </div>
                    </div>
                `;

                    const selResp = div.querySelector('.select-responsable-falla');
                    const inpNom = div.querySelector('.input-responsable-nombre');
                    if (selResp && inpNom) {
                        selResp.addEventListener('change', function() {
                            const opt = this.options[this.selectedIndex];
                            inpNom.value = opt?.dataset.nombre || '';
                        });
                    }

                    return div;
                }

                document.getElementById('btn-add-falla').addEventListener('click', function() {
                    container.appendChild(createFallaRow());
                    recalcularTotal();
                });

                container.addEventListener('click', function(e) {
                    const btn = e.target.closest('.btn-remove-falla');
                    if (btn) {
                        if (container.querySelectorAll('.falla-item').length > 1) {
                            btn.closest('.falla-item').remove();
                        } else {
                            alert('Debe haber al menos una falla.');
                        }
                        recalcularTotal();
                    }
                });

                container.addEventListener('input', function(e) {
                    if (e.target.classList.contains('falla-precio')) {
                        recalcularTotal();
                    }
                });

                // Inicializar
                container.appendChild(createFallaRow());
                recalcularTotal();

                // ============================================================
                // EVIDENCIAS — Acumular en memoria para subir al crear
                // ============================================================
                const EVIDENCIAS_TEMP = {
                    vehiculo: [],
                    fallas: {},
                };

                function fileToBase64(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(file);
                    });
                }

                function crearThumbTemp(item, contenedor, tipo, idxFalla) {
                    const div = document.createElement('div');
                    div.className =
                        'relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100';
                    div.dataset.fileName = item.file.name;
                    div.innerHTML = `
        <img src="${item.preview}" class="w-full h-full object-cover" alt="Evidencia">
        <button type="button"
            class="btn-eliminar-temp absolute top-1 right-1 w-6 h-6 rounded-lg bg-red-500/90 hover:bg-red-600 text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100"
            data-tipo="${tipo}"
            data-falla-idx="${idxFalla ?? ''}"
            data-file-name="${item.file.name}">
            <i class="fa-solid fa-trash text-[10px]"></i>
        </button>
    `;
                    contenedor.appendChild(div);
                }

                // Fotos del vehículo
                document.getElementById('input-evidencia-vehiculo-create')?.addEventListener('change',
            async function() {
                    const files = Array.from(this.files);
                    const galeria = document.getElementById('galeria-vehiculo-create');

                    for (const file of files) {
                        if (!file.type.startsWith('image/')) continue;
                        if (file.size > 10 * 1024 * 1024) {
                            alert(`${file.name} excede 10 MB`);
                            continue;
                        }
                        const base64 = await fileToBase64(file);
                        const item = {
                            file,
                            preview: base64
                        };
                        EVIDENCIAS_TEMP.vehiculo.push(item);
                        crearThumbTemp(item, galeria, 'vehiculo');
                    }
                    this.value = '';
                });

                // Evidencias por falla (delegación sobre container)
                container.addEventListener('change', async function(e) {
                    if (!e.target.classList.contains('input-evidencia-falla-create')) return;

                    const idx = e.target.dataset.fallaIdx;
                    const files = Array.from(e.target.files);
                    const galeria = e.target.closest('.md\\:col-span-3').querySelector(
                        '.evidencias-falla-create');

                    if (!EVIDENCIAS_TEMP.fallas[idx]) EVIDENCIAS_TEMP.fallas[idx] = [];

                    for (const file of files) {
                        if (!file.type.startsWith('image/')) continue;
                        if (file.size > 10 * 1024 * 1024) {
                            alert(`${file.name} excede 10 MB`);
                            continue;
                        }
                        const base64 = await fileToBase64(file);
                        const item = {
                            file,
                            preview: base64
                        };
                        EVIDENCIAS_TEMP.fallas[idx].push(item);
                        crearThumbTemp(item, galeria, 'falla', idx);
                    }
                    e.target.value = '';
                });

                // Eliminar thumb temporal
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.btn-eliminar-temp');
                    if (!btn) return;

                    const tipo = btn.dataset.tipo;
                    const idxFalla = btn.dataset.fallaIdx;
                    const fileName = btn.dataset.fileName;

                    if (tipo === 'vehiculo') {
                        EVIDENCIAS_TEMP.vehiculo = EVIDENCIAS_TEMP.vehiculo.filter(i => i.file.name !==
                            fileName);
                    } else if (EVIDENCIAS_TEMP.fallas[idxFalla]) {
                        EVIDENCIAS_TEMP.fallas[idxFalla] = EVIDENCIAS_TEMP.fallas[idxFalla].filter(i => i.file
                            .name !== fileName);
                    }
                    btn.closest('.aspect-square')?.remove();
                });

                // Al enviar: serializar en sessionStorage
                document.getElementById('orden-form').addEventListener('submit', function() {
                    const serializado = {
                        vehiculo: EVIDENCIAS_TEMP.vehiculo.map(i => ({
                            name: i.file.name,
                            type: i.file.type,
                            preview: i.preview,
                        })),
                        fallas: {},
                    };

                    Object.keys(EVIDENCIAS_TEMP.fallas).forEach(idx => {
                        serializado.fallas[idx] = EVIDENCIAS_TEMP.fallas[idx].map(i => ({
                            name: i.file.name,
                            type: i.file.type,
                            preview: i.preview,
                        }));
                    });

                    try {
                        if (serializado.vehiculo.length > 0 || Object.keys(serializado.fallas).length > 0) {
                            sessionStorage.setItem('evidencias_pendientes', JSON.stringify(serializado));
                        }
                    } catch (err) {
                        console.warn('No se pudieron guardar evidencias pendientes:', err);
                    }
                });
            });
        </script>
    @endpush
@endsection
