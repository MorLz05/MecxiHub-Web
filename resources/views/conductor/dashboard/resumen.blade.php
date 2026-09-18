@extends('conductor.layouts.cuenta')

@section('title', 'Resumen de Cuenta - MecxiHub')

@section('cuenta-content')
    <!-- Tarjeta de bienvenida -->
    <div class="bg-gradient-to-r from-[#0039A6] to-[#002677] rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-blue-200/80 text-sm">Bienvenido de nuevo</p>
                <h2 class="text-2xl font-bold">{{ session('firebase_user.nombre_completo') }}</h2>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    <span class="text-xs text-blue-200/80">{{ session('firebase_user.email') }}</span>
                    <span class="w-1 h-1 bg-blue-200/50 rounded-full"></span>
                    <span class="text-xs bg-white/20 px-2.5 py-0.5 rounded-full text-white">
                        Miembro desde {{ now()->format('d M, Y') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2.5 border border-white/20">
                <div class="text-center">
                    <p class="text-2xl font-bold">1,250</p>
                    <p class="text-[10px] text-blue-200/80 uppercase tracking-wider">Puntos MecxiHub</p>
                </div>
                <div class="w-px h-10 bg-white/20"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold">Base</p>
                    <p class="text-[10px] text-blue-200/80 uppercase tracking-wider">Plan activo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- === ACTIVIDAD RECIENTE === -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-clock-rotate-left text-[#FF6B00] text-lg"></i>
                <h3 class="font-bold text-gray-900">Actividad reciente</h3>
            </div>
            <a href="{{ route('cuenta.servicios.index') }}"
               class="text-xs text-[#0039A6] font-semibold hover:underline">
                Ver todos
            </a>
        </div>

        @php
            $estadoInfo = [
                'Pendiente' => [
                    'color' => 'blue',
                    'icono' => 'fa-solid fa-clipboard-list',
                    'texto' => 'Servicio registrado',
                ],
                'En reparación' => [
                    'color' => 'orange',
                    'icono' => 'fa-solid fa-wrench',
                    'texto' => 'En reparación',
                ],
                'Listo' => [
                    'color' => 'green',
                    'icono' => 'fa-solid fa-circle-check',
                    'texto' => 'Servicio completado',
                ],
                'Cancelado' => [
                    'color' => 'red',
                    'icono' => 'fa-solid fa-circle-xmark',
                    'texto' => 'Servicio cancelado',
                ],
            ];
        @endphp

        @if (empty($stats['ultimos']))
            <div class="text-center py-10">
                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-clock-rotate-left text-gray-400 text-xl"></i>
                </div>
                <p class="text-sm text-gray-500 font-medium">Aún no tienes actividad</p>
                <p class="text-xs text-gray-400 mt-1">
                    Cuando un taller registre un servicio para ti, aparecerá aquí.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($stats['ultimos'] as $orden)
                    @php
                        $estado = $orden['estado'] ?? 'Pendiente';
                        $info = $estadoInfo[$estado] ?? $estadoInfo['Pendiente'];
                        $vehiculo = $orden['vehiculo'] ?? [];
                        $vehiculoDesc = trim(
                            ($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? '') . ' ' . ($vehiculo['anio'] ?? '')
                        );
                        if (empty($vehiculoDesc)) $vehiculoDesc = 'Vehículo';
                        $placas = $vehiculo['placas'] ?? '';
                        $fecha = !empty($orden['fecha_orden'])
                            ? \Carbon\Carbon::parse($orden['fecha_orden'])->diffForHumans()
                            : 'Reciente';
                        $precio = (float) ($orden['precio_final'] ?? 0);
                    @endphp

                    <a href="{{ route('cuenta.servicios.detalle', ['id' => $orden['id']]) }}"
                       class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition">

                        {{-- Icono del estado --}}
                        <div class="w-11 h-11 rounded-xl bg-{{ $info['color'] }}-100 flex items-center justify-center flex-shrink-0">
                            <i class="{{ $info['icono'] }} text-{{ $info['color'] }}-600 text-base"></i>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-900">{{ $info['texto'] }}</p>
                                <span class="text-[10px] font-mono text-gray-400">
                                    {{ $orden['folio'] ?? '' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                {{ $vehiculoDesc }}
                                @if ($placas)
                                    <span class="text-gray-300">•</span>
                                    <span class="font-mono">{{ $placas }}</span>
                                @endif
                                @if (!empty($orden['taller_nombre']))
                                    <span class="text-gray-300">•</span>
                                    {{ $orden['taller_nombre'] }}
                                @endif
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $fecha }}</p>
                        </div>

                        {{-- Precio + flecha --}}
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if ($precio > 0)
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Total</p>
                                    <p class="text-sm font-bold text-gray-900">${{ number_format($precio, 2) }}</p>
                                </div>
                            @endif
                            <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Link al final --}}
            <div class="mt-5 pt-5 border-t border-gray-100 text-center">
                <a href="{{ route('cuenta.servicios.index') }}"
                   class="inline-flex items-center gap-2 text-sm text-[#0039A6] font-semibold hover:underline">
                    Ver todos mis servicios
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        @endif
    </div>

    <!-- === INFORMACIÓN RÁPIDA === -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-wrench text-[#0039A6]"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Servicios totales</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-spinner text-[#FF6B00]"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['activos'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">En proceso</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-store text-green-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['talleres_visitados'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Talleres visitados</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-circle-check text-purple-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['listos'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Completados</p>
        </div>
    </div>

    <!-- === ATENCIÓN AL CLIENTE === -->
    <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-headset text-[#FF6B00] text-xl"></i>
                    <h3 class="font-bold text-gray-900">Atención al cliente</h3>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    ¿Necesitas ayuda? Nuestro equipo está disponible para ti.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('asistente.ia') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF6B00] hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-comment-dots"></i>
                    Hablar con MecxiHub
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center py-4">
        <p class="text-xs text-gray-400">
            <i class="fa-solid fa-heart text-red-400 text-[10px]"></i>
            MecxiHub siempre contigo
        </p>
    </div>
@endsection