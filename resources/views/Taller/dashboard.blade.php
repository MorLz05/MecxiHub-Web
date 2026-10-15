@extends('taller.layouts.app')

@section('title', 'Resumen - Panel Taller')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Título -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Panel de tu taller</h1>
                <p class="text-gray-500 text-sm mt-1">Resumen general de tu taller y sus actividades.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- === TARJETA PRINCIPAL DEL TALLER === -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between flex-wrap gap-4">
                                <div class="flex items-center gap-4">
                                    @if (!empty($tallerData['logo_url']))
                                        <div class="w-16 h-16 rounded-2xl bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                            <img src="{{ $tallerData['logo_url'] }}" alt="{{ $tallerData['nombre'] }}"
                                                 class="w-full h-full object-contain p-1.5">
                                        </div>
                                    @else
                                        <div class="w-16 h-16 rounded-2xl bg-[#0039A6]/10 flex items-center justify-center flex-shrink-0">
                                            <i class="fa-solid fa-store text-3xl text-[#0039A6]"></i>
                                        </div>
                                    @endif

                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900">
                                            {{ $tallerData['nombre'] ?? session('firebase_user.nombre_completo') }}
                                        </h2>
                                        <div class="flex items-center gap-3 mt-1 flex-wrap">
                                            @if ($tallerData['verificado'] ?? false)
                                                <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-0.5 rounded-full text-xs font-semibold">
                                                    <i class="fa-solid fa-circle-check"></i> Verificado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 px-3 py-0.5 rounded-full text-xs font-semibold">
                                                    <i class="fa-solid fa-clock"></i> En verificación
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-[#0039A6] px-3 py-0.5 rounded-full text-xs font-semibold">
                                                <i class="fa-solid fa-crown"></i> {{ $tallerData['plan'] ?? 'Sin plan' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats inferiores inline --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-gray-100">
                                <div class="text-center">
                                    <p class="text-2xl font-black text-[#0039A6]">{{ $stats['ordenes_total'] }}</p>
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold mt-0.5">Órdenes totales</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-black text-green-600">{{ $stats['completadas'] }}</p>
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold mt-0.5">Completadas</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-black text-yellow-600">{{ $stats['pendientes'] + $stats['activas'] }}</p>
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold mt-0.5">En proceso</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-black text-[#FF6B00]">
                                        {{ $stats['calificacion'] > 0 ? number_format($stats['calificacion'], 1) : '--' }}
                                    </p>
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold mt-0.5">Calificación</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- === ESTADÍSTICAS RÁPIDAS === -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Estadísticas rápidas</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clipboard-list text-[#0039A6]"></i>
                                    <span class="text-sm text-gray-600">Órdenes hoy</span>
                                </div>
                                <span class="font-bold text-[#0039A6]">{{ $stats['ordenes_hoy'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-green-600"></i>
                                    <span class="text-sm text-gray-600">Completadas</span>
                                </div>
                                <span class="font-bold text-green-600">{{ $stats['completadas'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock text-yellow-600"></i>
                                    <span class="text-sm text-gray-600">Activas</span>
                                </div>
                                <span class="font-bold text-yellow-600">{{ $stats['activas'] + $stats['pendientes'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                                    <span class="text-sm text-gray-600">Canceladas</span>
                                </div>
                                <span class="font-bold text-red-500">{{ $stats['canceladas'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-star text-[#FF6B00]"></i>
                                    <span class="text-sm text-gray-600">Reseñas</span>
                                </div>
                                <span class="font-bold text-[#FF6B00]">{{ $stats['total_resenas'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- === GRID INFERIOR: ÓRDENES + RESEÑAS === -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

                <!-- ÓRDENES ACTIVAS -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-list text-[#0039A6]"></i>
                            Órdenes activas
                        </h3>
                        <a href="{{ route('taller.ordenes') }}"
                           class="text-xs text-[#0039A6] hover:underline font-semibold">
                            Ver todas <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    @if (empty($ordenesActivas))
                        <div class="text-center py-8">
                            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-inbox text-xl text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500">No hay órdenes activas</p>
                            <p class="text-xs text-gray-400 mt-0.5">Cuando recibas órdenes, aparecerán aquí</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($ordenesActivas as $orden)
                                @php
                                    $estado = $orden['estado'] ?? 'Pendiente';
                                    $colores = [
                                        'Pendiente' => 'bg-yellow-100 text-yellow-700',
                                        'En reparación' => 'bg-blue-100 text-brand-blue',
                                    ];
                                    $badge = $colores[$estado] ?? 'bg-gray-100 text-gray-600';
                                    $vehiculo = $orden['vehiculo'] ?? [];
                                @endphp
                                <a href="{{ route('taller.ordenes.show', $orden['id']) }}"
                                   class="flex items-center justify-between gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/30 transition group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-[#0039A6] transition">
                                            <i class="fa-solid fa-wrench text-[#0039A6] text-sm group-hover:text-white transition"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 text-sm truncate">
                                                {{ $vehiculo['marca'] ?? '' }} {{ $vehiculo['modelo'] ?? '' }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $orden['cliente_nombre'] ?? 'Sin cliente' }}
                                                @if (!empty($vehiculo['placas']))
                                                    · {{ $vehiculo['placas'] }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badge }}">
                                            {{ $estado }}
                                        </span>
                                        <p class="text-[10px] text-gray-400 mt-1 font-mono">
                                            {{ $orden['folio'] ?? '' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- ÚLTIMAS RESEÑAS -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-star text-[#FF6B00]"></i>
                            Últimas reseñas
                        </h3>
                        <a href="{{ route('taller.comentarios') }}"
                           class="text-xs text-[#0039A6] hover:underline font-semibold">
                            Ver todas <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    @if (empty($resenasRecientes))
                        <div class="text-center py-8">
                            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-star text-xl text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500">Aún no tienes reseñas</p>
                            <p class="text-xs text-gray-400 mt-0.5">Cuando tus clientes califiquen, aparecerán aquí</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($resenasRecientes as $resena)
                                @php
                                    $nombre = $resena['usuario_nombre'] ?? 'Cliente';
                                    $iniciales = '';
                                    foreach (preg_split('/\s+/', trim($nombre)) as $p) {
                                        if (!empty($p)) $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
                                        if (mb_strlen($iniciales) >= 2) break;
                                    }
                                    if (empty($iniciales)) $iniciales = 'C';
                                    $rating = (int) ($resena['rating'] ?? 0);
                                @endphp
                                <div class="p-3 rounded-xl border border-gray-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#0039A6] to-[#001B5E] text-white flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                            {{ $iniciales }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <p class="font-semibold text-gray-900 text-xs truncate">{{ $nombre }}</p>
                                                <div class="flex items-center gap-0.5">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fa-solid fa-star text-[9px] {{ $i <= $rating ? 'text-[#FF6B00]' : 'text-gray-300' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            @if (!empty($resena['comentario']))
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                    {{ $resena['comentario'] }}
                                                </p>
                                            @endif
                                            @if (!empty($resena['respuesta_taller']))
                                                <p class="text-[10px] text-green-700 mt-1.5 flex items-center gap-1">
                                                    <i class="fa-solid fa-check-circle"></i> Respondida
                                                </p>
                                            @else
                                                <a href="{{ route('taller.comentarios') }}"
                                                   class="text-[10px] text-[#FF6B00] mt-1.5 inline-flex items-center gap-1 hover:underline font-semibold">
                                                    <i class="fa-solid fa-reply"></i> Responder
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- === ÓRDENES DE HOY === -->
            @if (!empty($ordenesHoy))
                <div class="mt-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-day text-[#0039A6]"></i>
                                Órdenes de hoy ({{ count($ordenesHoy) }})
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($ordenesHoy as $orden)
                                @php
                                    $estado = $orden['estado'] ?? 'Pendiente';
                                    $colores = [
                                        'Pendiente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'En reparación' => 'bg-blue-100 text-brand-blue border-blue-200',
                                        'Listo' => 'bg-green-100 text-green-700 border-green-200',
                                        'Cancelado' => 'bg-red-100 text-red-700 border-red-200',
                                    ];
                                    $badge = $colores[$estado] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                                    $vehiculo = $orden['vehiculo'] ?? [];
                                @endphp
                                <a href="{{ route('taller.ordenes.show', $orden['id']) }}"
                                   class="block p-3 rounded-xl border {{ $badge }} hover:shadow-md transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-mono text-[10px] font-bold">
                                            {{ $orden['folio'] ?? '' }}
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/60">
                                            {{ $estado }}
                                        </span>
                                    </div>
                                    <p class="font-semibold text-sm truncate">
                                        {{ $vehiculo['marca'] ?? '' }} {{ $vehiculo['modelo'] ?? '' }}
                                    </p>
                                    <p class="text-xs opacity-80 truncate">
                                        {{ $orden['cliente_nombre'] ?? 'Sin cliente' }}
                                    </p>
                                    <p class="text-[10px] opacity-70 mt-1 font-mono">
                                        {{ strtoupper($vehiculo['placas'] ?? '') }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection