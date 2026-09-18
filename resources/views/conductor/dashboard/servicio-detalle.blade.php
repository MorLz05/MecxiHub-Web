@extends('conductor.layouts.cuenta')

@section('title', 'Detalle de servicio - MecxiHub')

@section('cuenta-content')

    @php
        $estado = $orden['estado'] ?? 'Pendiente';
        $estadoConfig = [
            'Pendiente'     => ['bg-yellow-100', 'text-yellow-700', 'fa-clock',        'Pendiente'],
            'En reparación' => ['bg-blue-100',   'text-blue-700',   'fa-wrench',       'En reparación'],
            'Listo'         => ['bg-green-100',  'text-green-700',  'fa-circle-check', 'Completado'],
            'Cancelado'     => ['bg-red-100',    'text-red-700',    'fa-circle-xmark', 'Cancelado'],
        ];
        $conf = $estadoConfig[$estado] ?? $estadoConfig['Pendiente'];

        $v = $orden['vehiculo'] ?? [];
        $vehiculoDesc = trim(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? '') . ' ' . ($v['anio'] ?? ''));
        $placas = $v['placas'] ?? '';

        $precio = (float) ($orden['precio_final'] ?? 0);
        $anticipo = (float) ($orden['presupuesto']['anticipo'] ?? 0);
        $saldo = max(0, $precio - $anticipo);

        $fechaOrden = !empty($orden['fecha_orden'])
            ? \Carbon\Carbon::parse($orden['fecha_orden'])
            : null;
        $fechaEstimada = !empty($orden['fecha_entrega_estimada'])
            ? \Carbon\Carbon::parse($orden['fecha_entrega_estimada'])
            : null;
        $fechaReal = !empty($orden['fecha_entrega_real'])
            ? \Carbon\Carbon::parse($orden['fecha_entrega_real'])
            : null;
    @endphp

    {{-- Botón volver --}}
    <div class="mb-4">
        <a href="{{ route('cuenta.servicios.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#0039A6] font-semibold transition">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a mis servicios
        </a>
    </div>

    {{-- Header de la orden --}}
    <div class="bg-gradient-to-br from-[#0039A6] to-[#002677] rounded-3xl shadow-lg p-6 sm:p-8 text-white mb-6 relative overflow-hidden">
        {{-- Decoración --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-[#FF6B00]/10 rounded-full translate-y-24 -translate-x-24"></div>

        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs font-bold text-blue-200 uppercase tracking-wider">
                            Orden {{ $orden['folio'] ?? '—' }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 backdrop-blur-sm uppercase tracking-wider">
                            <i class="fa-solid {{ $conf[2] }} text-[9px]"></i>
                            {{ $conf[3] }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black mt-2">
                        {{ $vehiculoDesc }}
                    </h1>

                    <div class="flex items-center gap-3 mt-2 flex-wrap text-sm text-blue-100">
                        @if ($placas)
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-id-card text-[#FF6B00]"></i>
                                {{ $placas }}
                            </span>
                        @endif
                        @if ($fechaOrden)
                            <span class="text-blue-300/50">|</span>
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-[#FF6B00]"></i>
                                {{ $fechaOrden->format('d M, Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="text-right sm:text-right bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-3 border border-white/20">
                    <p class="text-xs text-blue-100 uppercase tracking-wider font-bold">Total</p>
                    <p class="text-3xl font-black">${{ number_format($precio, 2) }}</p>
                    @if ($anticipo > 0)
                        <p class="text-[10px] text-blue-200 mt-1">Anticipo: ${{ number_format($anticipo, 2) }}</p>
                    @endif
                </div>
            </div>

            {{-- Timeline del estado --}}
            @if ($estado !== 'Cancelado')
                @php
                    $pasos = ['Pendiente', 'En reparación', 'Listo'];
                    $indexActual = array_search($estado, $pasos);
                    if ($indexActual === false) $indexActual = 0;
                @endphp
                <div class="mt-6 pt-6 border-t border-white/10">
                    <div class="flex items-center justify-between relative">
                        {{-- Línea de fondo --}}
                        <div class="absolute top-4 left-0 right-0 h-0.5 bg-white/15"></div>
                        <div class="absolute top-4 left-0 h-0.5 bg-[#FF6B00] transition-all duration-700"
                             style="width: {{ ($indexActual / (count($pasos) - 1)) * 100 }}%"></div>

                        @foreach ($pasos as $i => $paso)
                            @php
                                $completado = $i <= $indexActual;
                                $actual = $i === $indexActual;
                            @endphp
                            <div class="relative flex flex-col items-center z-10 flex-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition
                                    {{ $completado ? 'bg-[#FF6B00] border-[#FF6B00]' : 'bg-[#002677] border-white/30' }}
                                    {{ $actual ? 'ring-4 ring-[#FF6B00]/30' : '' }}">
                                    @if ($completado)
                                        <i class="fa-solid fa-check text-white text-xs"></i>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-white/30"></span>
                                    @endif
                                </div>
                                <p class="text-[10px] sm:text-xs font-bold mt-2 uppercase tracking-wider text-center
                                    {{ $completado ? 'text-white' : 'text-blue-200/60' }}">
                                    {{ $paso }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Taller --}}
            @if (!empty($orden['taller_id']))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-1.5 h-6 bg-[#FF6B00] rounded-full"></span>
                        <h2 class="font-bold text-gray-900">Taller</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center flex-shrink-0">
                            @if (!empty($tallerData['logo_url']))
                                <img src="{{ $tallerData['logo_url'] }}"
                                     alt="{{ $tallerData['nombre'] ?? 'Taller' }}"
                                     class="w-full h-full object-cover rounded-2xl">
                            @else
                                <i class="fa-solid fa-store text-white text-xl"></i>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900">
                                {{ $tallerData['nombre'] ?? ($orden['taller_nombre'] ?? 'Taller') }}
                            </h3>
                            @if (!empty($tallerData['direccion']))
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-gray-400 mr-1"></i>
                                    {{ $tallerData['direccion'] }}
                                </p>
                            @endif
                            @if (!empty($tallerData['telefono']))
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fa-solid fa-phone text-gray-400 mr-1"></i>
                                    {{ $tallerData['telefono'] }}
                                </p>
                            @endif
                        </div>

                        <a href="{{ route('taller.perfil', ['id' => $orden['taller_id']]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 text-[#0039A6] text-xs font-semibold hover:bg-blue-100 transition whitespace-nowrap">
                            Ver perfil
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Fallas / Servicios --}}
            @if (!empty($fallas))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-1.5 h-6 bg-[#FF6B00] rounded-full"></span>
                        <h2 class="font-bold text-gray-900">Servicios realizados</h2>
                        <span class="ml-auto text-xs text-gray-400 font-semibold">
                            {{ count($fallas) }} {{ count($fallas) === 1 ? 'servicio' : 'servicios' }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($fallas as $falla)
                            @php
                                $prioridad = $falla['prioridad'] ?? 'Media';
                                $prioridadColor = match($prioridad) {
                                    'Baja'    => 'bg-gray-100 text-gray-600',
                                    'Media'   => 'bg-blue-100 text-blue-700',
                                    'Alta'    => 'bg-orange-100 text-orange-700',
                                    'Urgente' => 'bg-red-100 text-red-700',
                                    default   => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i class="fa-solid fa-wrench text-[#0039A6] text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">
                                                {{ $falla['categoria'] ?? 'Servicio' }}
                                            </h4>
                                            @if (!empty($falla['descripcion']))
                                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                                    {{ $falla['descripcion'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="font-bold text-gray-900 text-sm">
                                                ${{ number_format((float) ($falla['precio'] ?? 0), 2) }}
                                            </p>
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $prioridadColor }} uppercase tracking-wider mt-1">
                                                {{ $prioridad }}
                                            </span>
                                        </div>
                                    </div>

                                    @if (!empty($falla['responsable']['nombre']))
                                        <div class="mt-2 pt-2 border-t border-gray-200 flex items-center gap-1.5 text-xs text-gray-500">
                                            <i class="fa-solid fa-user-wrench text-gray-400"></i>
                                            Asignado a: <strong class="text-gray-700">{{ $falla['responsable']['nombre'] }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Vehículo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-1.5 h-6 bg-[#FF6B00] rounded-full"></span>
                    <h2 class="font-bold text-gray-900">Datos del vehículo</h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Marca</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $v['marca'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Modelo</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $v['modelo'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Año</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $v['anio'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Placas</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5 font-mono">{{ $placas ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Color</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $v['color'] ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Kilometraje</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">
                            {{ !empty($v['kilometraje']) ? number_format((int) $v['kilometraje']) . ' km' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- CTA Reseña (si está Listo y no ha reseñado) --}}
            @if ($estado === 'Listo' && !$yaReseno && !empty($orden['taller_id']))
                <div class="bg-gradient-to-r from-[#FF6B00] to-orange-500 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 border border-white/30">
                                <i class="fa-solid fa-star text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">¿Cómo te fue en este servicio?</h3>
                                <p class="text-sm text-orange-100 mt-0.5">
                                    Deja tu reseña y ayuda a otros conductores a elegir mejor.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('taller.perfil', ['id' => $orden['taller_id']]) }}#seccion-resenas"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white text-[#FF6B00] font-bold text-sm hover:bg-orange-50 transition whitespace-nowrap shadow-md">
                            <i class="fa-solid fa-pen"></i>
                            Dejar reseña
                        </a>
                    </div>
                </div>
            @endif

            {{-- Ya dejó reseña --}}
            @if ($estado === 'Listo' && $yaReseno)
                <div class="bg-green-50 rounded-2xl border border-green-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-green-800 text-sm">¡Gracias por tu reseña!</h3>
                        <p class="text-xs text-green-700 mt-0.5">
                            Tu opinión ayuda a mejorar la comunidad MecxiHub.
                        </p>
                    </div>
                    <a href="{{ route('taller.perfil', ['id' => $orden['taller_id']]) }}#seccion-resenas"
                       class="text-xs font-semibold text-green-700 hover:underline whitespace-nowrap">
                        Ver mi reseña
                    </a>
                </div>
            @endif

        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Resumen de pago --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#0039A6]"></i>
                    Resumen de pago
                </h3>

                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-semibold text-gray-900">${{ number_format($precio, 2) }}</span>
                    </div>
                    @if ($anticipo > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Anticipo</span>
                            <span class="font-semibold text-green-600">-${{ number_format($anticipo, 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t border-dashed border-gray-200 pt-2.5 flex justify-between">
                        <span class="font-bold text-gray-700">Saldo pendiente</span>
                        <span class="font-black text-lg text-[#FF6B00]">${{ number_format($saldo, 2) }}</span>
                    </div>
                </div>

                @if (!empty($orden['presupuesto']['metodo_pago']))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Método de pago</p>
                        <p class="text-sm font-semibold text-gray-700">{{ $orden['presupuesto']['metodo_pago'] }}</p>
                    </div>
                @endif
            </div>

            {{-- Fechas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-[#0039A6]"></i>
                    Fechas clave
                </h3>

                <div class="space-y-3">
                    @if ($fechaOrden)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-clipboard-list text-[#0039A6] text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Registro</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $fechaOrden->format('d M, Y') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($fechaEstimada)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-hourglass-half text-[#FF6B00] text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Entrega estimada</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $fechaEstimada->format('d M, Y') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($fechaReal)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-circle-check text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Entrega real</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $fechaReal->format('d M, Y') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contacto taller --}}
            @if (!empty($tallerData['telefono']))
                @php
                    $telLimpio = preg_replace('/[^0-9]/', '', $tallerData['telefono']);
                @endphp
                <a href="https://wa.me/52{{ $telLimpio }}" target="_blank" rel="noopener"
                   class="block w-full bg-[#FF6B00] hover:bg-orange-600 text-white rounded-2xl shadow-md p-5 transition text-center">
                    <i class="fa-brands fa-whatsapp text-3xl"></i>
                    <p class="font-bold mt-2 text-sm">Contactar al taller</p>
                    <p class="text-xs text-orange-100 mt-0.5">Vía WhatsApp</p>
                </a>
            @endif

        </div>
    </div>

@endsection
