@extends('conductor.layouts.cuenta')

@section('title', 'Mis Vehículos - MecxiHub')

@section('cuenta-content')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Mis vehículos</h3>
                <p class="text-sm text-gray-500">Gestiona los vehículos registrados en tu cuenta.</p>
            </div>
            <a href="{{ route('cuenta.vehiculos.crear') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#FF6B00] hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-orange-500/30 whitespace-nowrap">
                <i class="fa-solid fa-plus"></i>
                Agregar vehículo
            </a>
        </div>

        <div class="p-6">
            @if (empty($vehiculos))
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-car text-3xl text-gray-400"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-700">No tienes vehículos registrados</h4>
                    <p class="text-sm text-gray-500 mt-1">Agrega tu primer vehículo para comenzar.</p>
                    <a href="{{ route('cuenta.vehiculos.crear') }}"
                        class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 bg-[#0039A6] hover:bg-[#002B80] text-white rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        Agregar vehículo
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($vehiculos as $vehiculo)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-[#0039A6]/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-car text-[#0039A6] text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $vehiculo['marca'] }}
                                            {{ $vehiculo['modelo'] }}</h4>
                                        <p class="text-xs text-gray-500">{{ $vehiculo['año'] }} • {{ $vehiculo['placas'] }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full {{ $vehiculo['activo'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $vehiculo['activo'] ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">
                                    {{ $vehiculo['color'] }}
                                </span>
                                @if (!empty($vehiculo['kilometraje_actual']))
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">
                                        {{ number_format($vehiculo['kilometraje_actual']) }} km
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <a href="{{ route('cuenta.vehiculos.ver', $vehiculo['id']) }}"
                                    class="flex-1 text-center py-2 px-3 bg-blue-50 hover:bg-blue-100 text-[#0039A6] rounded-lg font-medium text-xs transition">
                                    Ver detalle
                                </a>
                                <a href="{{ route('cuenta.vehiculos.editar', $vehiculo['id']) }}"
                                    class="flex-1 text-center py-2 px-3 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg font-medium text-xs transition">
                                    Editar
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
