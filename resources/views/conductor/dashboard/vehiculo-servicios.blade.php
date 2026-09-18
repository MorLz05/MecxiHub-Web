@extends('conductor.layouts.cuenta')

@section('title', 'Servicios - ' . $vehiculo['marca'] . ' ' . $vehiculo['modelo'])

@section('cuenta-content')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <div>
                <a href="{{ route('cuenta.vehiculos.ver', $vehiculo['id']) }}" class="text-sm text-[#0039A6] hover:underline">
                    <i class="fa-solid fa-arrow-left"></i> Volver al vehículo
                </a>
                <h3 class="text-lg font-bold text-gray-900 mt-1">
                    Historial de servicios - {{ $vehiculo['marca'] }} {{ $vehiculo['modelo'] }}
                </h3>
                <p class="text-sm text-gray-500">{{ $vehiculo['placas'] }}</p>
            </div>
        </div>

        <div class="p-6">
            @if (empty($servicios))
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-wrench text-3xl text-gray-400"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-700">Sin servicios registrados</h4>
                    <p class="text-sm text-gray-500 mt-1">Este vehículo aún no tiene servicios registrados.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Fecha</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Servicio</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Taller</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Kilometraje</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Costo</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servicios as $servicio)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">
                                        {{ \Carbon\Carbon::parse($servicio['fecha_servicio'])->format('d M, Y') }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $servicio['tipo_servicio'] ?? 'Servicio' }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $servicio['taller_nombre'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ number_format($servicio['kilometraje'] ?? 0) }} km</td>
                                    <td class="py-3 px-4">${{ number_format($servicio['costo'] ?? 0, 2) }}</td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full
                                            @if (($servicio['estado'] ?? '') === 'completado') bg-green-100 text-green-700
                                            @elseif(($servicio['estado'] ?? '') === 'en_proceso') bg-blue-100 text-blue-700
                                            @elseif(($servicio['estado'] ?? '') === 'pendiente') bg-yellow-100 text-yellow-700
                                            @else bg-gray-200 text-gray-600 @endif">
                                            {{ ucfirst($servicio['estado'] ?? 'pendiente') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
