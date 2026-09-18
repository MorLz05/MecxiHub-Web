@extends('conductor.layouts.cuenta')

@section('title', $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - MecxiHub')

@section('cuenta-content')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('cuenta.vehiculos') }}" class="text-sm text-[#0039A6] hover:underline">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
                <h3 class="text-lg font-bold text-gray-900 mt-1">
                    {{ $vehiculo['marca'] }} {{ $vehiculo['modelo'] }}
                </h3>
                <p class="text-sm text-gray-500">{{ $vehiculo['año'] }} • {{ $vehiculo['placas'] }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="text-xs px-3 py-1 rounded-full {{ $vehiculo['activo'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                    {{ $vehiculo['activo'] ? 'Activo' : 'Inactivo' }}
                </span>
                <a href="{{ route('cuenta.vehiculos.editar', $vehiculo['id']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#0039A6] hover:bg-[#002B80] text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-pen"></i>
                    Editar
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info del vehículo -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3">Información del vehículo</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Marca</span>
                            <span class="font-medium">{{ $vehiculo['marca'] }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Modelo</span>
                            <span class="font-medium">{{ $vehiculo['modelo'] }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Año</span>
                            <span class="font-medium">{{ $vehiculo['año'] }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Placas</span>
                            <span class="font-medium">{{ $vehiculo['placas'] }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Color</span>
                            <span class="font-medium">{{ $vehiculo['color'] }}</span>
                        </div>
                        @if (!empty($vehiculo['vin']))
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">VIN</span>
                                <span class="font-medium">{{ $vehiculo['vin'] }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Kilometraje</span>
                            <span class="font-medium">{{ number_format($vehiculo['kilometraje_actual'] ?? 0) }} km</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-500">Registrado</span>
                            <span
                                class="font-medium">{{ \Carbon\Carbon::parse($vehiculo['fecha_registro'])->format('d M, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Servicios recientes -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900">Servicios recientes</h4>
                        <a href="{{ route('cuenta.vehiculos.servicios', $vehiculo['id']) }}"
                            class="text-xs text-[#0039A6] hover:underline">
                            Ver todos
                        </a>
                    </div>

                    @if (empty($servicios))
                        <div class="text-center py-8 bg-gray-50 rounded-xl">
                            <i class="fa-solid fa-wrench text-3xl text-gray-300"></i>
                            <p class="text-sm text-gray-500 mt-2">Sin servicios registrados</p>
                            <p class="text-xs text-gray-400">Los servicios aparecerán aquí</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach (array_slice($servicios, 0, 5) as $servicio)
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $servicio['tipo_servicio'] ?? 'Servicio' }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($servicio['fecha_servicio'])->format('d M, Y') }}
                                                @if (!empty($servicio['kilometraje']))
                                                    • {{ number_format($servicio['kilometraje']) }} km
                                                @endif
                                            </p>
                                        </div>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full
                                            @if ($servicio['estado'] === 'completado') bg-green-100 text-green-700
                                            @elseif($servicio['estado'] === 'en_proceso') bg-blue-100 text-blue-700
                                            @elseif($servicio['estado'] === 'pendiente') bg-yellow-100 text-yellow-700
                                            @else bg-gray-200 text-gray-600 @endif">
                                            {{ ucfirst($servicio['estado'] ?? 'pendiente') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
