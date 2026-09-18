@extends('conductor.layouts.cuenta')

@section('title', 'Editar Vehículo - MecxiHub')

@section('cuenta-content')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <div>
                <a href="{{ route('cuenta.vehiculos') }}" class="text-sm text-[#0039A6] hover:underline">
                    <i class="fa-solid fa-arrow-left"></i> Volver a mis vehículos
                </a>
                <h3 class="text-lg font-bold text-gray-900 mt-1">Editar vehículo</h3>
                <p class="text-sm text-gray-500">Actualiza la información de tu vehículo.</p>
            </div>
        </div>

        <div class="p-6">
            <form action="{{ route('cuenta.vehiculos.update', $vehiculo['id']) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Marca -->
                    <div>
                        <label for="marca" class="block text-sm font-semibold text-gray-700 mb-1.5">Marca <span
                                class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-tag"></i>
                            </span>
                            <input type="text" id="marca" name="marca"
                                value="{{ old('marca', $vehiculo['marca']) }}" placeholder="Ej. Toyota, Honda, Ford"
                                required
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('marca')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label for="modelo" class="block text-sm font-semibold text-gray-700 mb-1.5">Modelo <span
                                class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-car"></i>
                            </span>
                            <input type="text" id="modelo" name="modelo"
                                value="{{ old('modelo', $vehiculo['modelo']) }}" placeholder="Ej. Corolla, Civic, Mustang"
                                required
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('modelo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Año -->
                    <div>
                        <label for="año" class="block text-sm font-semibold text-gray-700 mb-1.5">Año <span
                                class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-calendar"></i>
                            </span>
                            <input type="number" id="año" name="año" value="{{ old('año', $vehiculo['año']) }}"
                                placeholder="2024" min="1900" max="{{ date('Y') + 1 }}" required
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('año')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Placas -->
                    <div>
                        <label for="placas" class="block text-sm font-semibold text-gray-700 mb-1.5">Placas <span
                                class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <input type="text" id="placas" name="placas"
                                value="{{ old('placas', $vehiculo['placas']) }}" placeholder="ABC-1234" required
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('placas')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-semibold text-gray-700 mb-1.5">Color <span
                                class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-palette"></i>
                            </span>
                            <input type="text" id="color" name="color"
                                value="{{ old('color', $vehiculo['color']) }}" placeholder="Ej. Rojo, Azul, Blanco"
                                required
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('color')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VIN (opcional) -->
                    <div>
                        <label for="vin" class="block text-sm font-semibold text-gray-700 mb-1.5">VIN <span
                                class="text-gray-400 text-xs">(opcional)</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input type="text" id="vin" name="vin"
                                value="{{ old('vin', $vehiculo['vin'] ?? '') }}" placeholder="17 caracteres" maxlength="17"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('vin')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kilometraje actual -->
                    <div>
                        <label for="kilometraje_actual" class="block text-sm font-semibold text-gray-700 mb-1.5">Kilometraje
                            actual <span class="text-gray-400 text-xs">(opcional)</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-road"></i>
                            </span>
                            <input type="number" id="kilometraje_actual" name="kilometraje_actual"
                                value="{{ old('kilometraje_actual', $vehiculo['kilometraje_actual'] ?? 0) }}"
                                placeholder="0" min="0"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        </div>
                        @error('kilometraje_actual')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Activo -->
                    <div class="flex items-center gap-3 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="activo" value="1"
                                {{ old('activo', $vehiculo['activo'] ?? true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-[#0039A6] focus:ring-[#0039A6]">
                            <span class="text-sm font-medium text-gray-700">Vehículo activo</span>
                        </label>
                        <span class="text-xs text-gray-400">(Desactívalo si ya no lo usas)</span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-[#FF6B00] hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-orange-500/30">
                        <i class="fa-solid fa-save"></i>
                        Actualizar vehículo
                    </button>
                    <a href="{{ route('cuenta.vehiculos') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                        Cancelar
                    </a>

                    <!-- Botón de eliminar -->
                    <button type="button" onclick="confirmDelete('{{ $vehiculo['id'] }}')"
                        class="ml-auto inline-flex items-center gap-2 px-6 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-trash"></i>
                        Eliminar
                    </button>
                </div>
            </form>

            <!-- Formulario oculto para eliminar -->
            <form id="delete-form" action="{{ route('cuenta.vehiculos.destroy', $vehiculo['id']) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este vehículo? Esta acción no se puede deshacer.')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
@endsection
