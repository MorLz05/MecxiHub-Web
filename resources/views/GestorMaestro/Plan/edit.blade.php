@extends('GestorMaestro.layouts.app')

@section('title', 'Editar Plan - MecxiHub')
@section('header-title', 'Editar Plan')
@section('header-subtitle', 'Modifica los datos del plan')

@section('content')

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

    <form action="{{ route('gestor.planes.update', $plan['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-brand-blue"></i>
                        Información del Plan
                    </h3>



                    <!-- Tipos de usuario -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-users-gear text-brand-blue"></i>
                            Dirigido a
                        </h3>

                        @php
                            $tiposDisponibles = ['Conductor', 'Taller'];
                            $tiposSeleccionados = old('tipos_usuario', $plan['tipos_usuario'] ?? []);
                        @endphp

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach ($tiposDisponibles as $tipo)
                                <label
                                    class="relative flex items-center gap-2 px-4 py-3 rounded-xl border-2 cursor-pointer transition
                {{ in_array($tipo, $tiposSeleccionados) ? 'border-brand-blue bg-brand-blue/5' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="checkbox" name="tipos_usuario[]" value="{{ $tipo }}"
                                        {{ in_array($tipo, $tiposSeleccionados) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded text-brand-blue focus:ring-brand-blue tipo-checkbox">
                                    <span class="text-sm font-medium text-gray-700">{{ $tipo }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Selecciona al menos un tipo de usuario al que va dirigido este
                            plan.</p>
                        @error('tipos_usuario')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="nombre"
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Nombre del Plan *
                            </label>
                            <input type="text" id="nombre" name="nombre"
                                value="{{ old('nombre', $plan['nombre'] ?? '') }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>

                        <div>
                            <label for="descripcion"
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Descripción
                            </label>
                            <textarea id="descripcion" name="descripcion" rows="3"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">{{ old('descripcion', $plan['descripcion'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-brand-orange"></i>
                            Características
                        </h3>
                        <button type="button" id="btn-add-caracteristica"
                            class="px-3 py-1.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-plus mr-1"></i> Agregar
                        </button>
                    </div>

                    <div id="caracteristicas-container" class="space-y-2">
                        @php
                            $oldCars = old('caracteristicas', $plan['caracteristicas'] ?? ['']);
                            if (empty($oldCars)) {
                                $oldCars = [''];
                            }
                        @endphp
                        @foreach ($oldCars as $car)
                            <div class="caracteristica-item flex gap-2">
                                <span class="flex-shrink-0 w-8 h-10 flex items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-grip-vertical text-xs"></i>
                                </span>
                                <input type="text" name="caracteristicas[]" value="{{ $car }}"
                                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                                <button type="button"
                                    class="btn-remove-car flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 flex items-center justify-center transition">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-dollar-sign text-green-600"></i>
                        Precio y Duración
                    </h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <label for="precio"
                                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                    Precio *
                                </label>
                                <input type="number" step="0.01" min="0" id="precio" name="precio"
                                    value="{{ old('precio', $plan['precio'] ?? 0) }}" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                            </div>
                            <div>
                                <label for="moneda"
                                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                    Moneda
                                </label>
                                <select id="moneda" name="moneda"
                                    class="w-full px-2 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                                    @foreach (['USD', 'MXN', 'EUR'] as $mon)
                                        <option value="{{ $mon }}"
                                            {{ old('moneda', $plan['moneda'] ?? 'USD') === $mon ? 'selected' : '' }}>
                                            {{ $mon }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="duracion_dias"
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Duración (días) *
                            </label>
                            <input type="number" min="1" id="duracion_dias" name="duracion_dias"
                                value="{{ old('duracion_dias', $plan['duracion_dias'] ?? 30) }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>

                        <div>
                            <label for="orden"
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Orden de visualización
                            </label>
                            <input type="number" min="0" id="orden" name="orden"
                                value="{{ old('orden', $plan['orden'] ?? 0) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-toggle-on text-brand-blue"></i>
                        Opciones
                    </h3>

                    <div class="space-y-3">
                        <label class="flex items-center justify-between gap-3 cursor-pointer">
                            <span class="text-sm text-gray-700 font-medium">Plan activo</span>
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1"
                                {{ old('activo', $plan['activo'] ?? true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-brand-blue focus:ring-brand-blue">
                        </label>

                        <label class="flex items-center justify-between gap-3 cursor-pointer">
                            <span class="text-sm text-gray-700 font-medium">Marcar como destacado</span>
                            <input type="hidden" name="destacado" value="0">
                            <input type="checkbox" name="destacado" value="1"
                                {{ old('destacado', $plan['destacado'] ?? false) ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-brand-orange focus:ring-brand-orange">
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col gap-2">
                    <button type="submit"
                        class="w-full px-6 py-3 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Actualizar Plan
                    </button>
                    <a href="{{ route('gestor.planes') }}"
                        class="w-full text-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                        Cancelar
                    </a>
                </div>

            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('caracteristicas-container');

                function createRow(value = '') {
                    const div = document.createElement('div');
                    div.className = 'caracteristica-item flex gap-2';
                    div.innerHTML = `
                        <span class="flex-shrink-0 w-8 h-10 flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-grip-vertical text-xs"></i>
                        </span>
                        <input type="text" name="caracteristicas[]" value="${value}"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        <button type="button"
                            class="btn-remove-car flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 flex items-center justify-center transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    `;
                    return div;
                }

                document.getElementById('btn-add-caracteristica').addEventListener('click', function() {
                    container.appendChild(createRow());
                });

                // Estilo visual dinámico para los checkboxes de tipo
                document.querySelectorAll('.tipo-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const label = this.closest('label');
                        if (this.checked) {
                            label.classList.add('border-brand-blue', 'bg-brand-blue/5');
                            label.classList.remove('border-gray-200', 'hover:border-gray-300');
                        } else {
                            label.classList.remove('border-brand-blue', 'bg-brand-blue/5');
                            label.classList.add('border-gray-200', 'hover:border-gray-300');
                        }
                    });
                });

                container.addEventListener('click', function(e) {
                    const btn = e.target.closest('.btn-remove-car');
                    if (btn) {
                        const items = container.querySelectorAll('.caracteristica-item');
                        if (items.length > 1) {
                            btn.closest('.caracteristica-item').remove();
                        } else {
                            btn.closest('.caracteristica-item').querySelector('input').value = '';
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
