@extends('conductor.layouts.cuenta')

@section('title', 'Mis Servicios - MecxiHub')

@section('cuenta-content')

    {{-- Header con acciones --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#0039A6]/10 flex items-center justify-center">
                    <i class="fa-solid fa-wrench text-[#0039A6] text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Mis servicios</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Historial y seguimiento de todos los servicios de tus vehículos.
                    </p>
                </div>
            </div>
            <a href="{{ route('buscar.talleres') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FF6B00] text-white font-semibold text-sm hover:bg-orange-600 transition shadow-sm">
                <i class="fa-solid fa-plus"></i>
                Agendar nuevo servicio
            </a>
        </div>
    </div>

    {{-- Stats rápidas --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-list-check text-[#0039A6]"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-spinner text-[#FF6B00]"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['activos'] }}</p>
                    <p class="text-xs text-gray-500">En proceso</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-green-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['listos'] }}</p>
                    <p class="text-xs text-gray-500">Completados</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-xmark text-red-500"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['cancelados'] }}</p>
                    <p class="text-xs text-gray-500">Cancelados</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista --}}
    @if (empty($ordenes))
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-car-burst text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Aún no tienes servicios</h3>
            <p class="text-gray-500 text-sm mt-1">
                Cuando agendes un servicio en un taller, aparecerá aquí.
            </p>
            <a href="{{ route('buscar.talleres') }}"
               class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 bg-[#0039A6] hover:bg-blue-800 text-white rounded-xl font-semibold text-sm transition">
                <i class="fa-solid fa-magnifying-glass"></i>
                Buscar talleres
            </a>
        </div>
    @else
        {{-- Filtros rápidos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-3 mb-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider px-2">Filtrar:</span>
            <button type="button" data-filtro="todos"
                    class="filtro-btn px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#0039A6] text-white transition">
                Todos ({{ count($ordenes) }})
            </button>
            <button type="button" data-filtro="Pendiente"
                    class="filtro-btn px-3.5 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                Pendientes ({{ $stats['activos'] }})
            </button>
            <button type="button" data-filtro="Listo"
                    class="filtro-btn px-3.5 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                Completados ({{ $stats['listos'] }})
            </button>
            <button type="button" data-filtro="Cancelado"
                    class="filtro-btn px-3.5 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                Cancelados ({{ $stats['cancelados'] }})
            </button>
        </div>

        <div class="space-y-4" id="lista-servicios">
            @foreach ($ordenes as $orden)
                @php
                    $estado = $orden['estado'] ?? 'Pendiente';
                    $estadoConfig = [
                        'Pendiente'     => ['bg-yellow-100', 'text-yellow-700', 'fa-clock',          'Pendiente'],
                        'En reparación' => ['bg-blue-100',   'text-blue-700',   'fa-wrench',         'En reparación'],
                        'Listo'         => ['bg-green-100',  'text-green-700',  'fa-circle-check',   'Completado'],
                        'Cancelado'     => ['bg-red-100',    'text-red-700',    'fa-circle-xmark',   'Cancelado'],
                    ];
                    $conf = $estadoConfig[$estado] ?? $estadoConfig['Pendiente'];

                    $v = $orden['vehiculo'] ?? [];
                    $vehiculoDesc = trim(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? '') . ' ' . ($v['anio'] ?? ''));
                    if (empty($vehiculoDesc)) $vehiculoDesc = 'Vehículo';
                    $placas = $v['placas'] ?? '';

                    $fecha = !empty($orden['fecha_orden'])
                        ? \Carbon\Carbon::parse($orden['fecha_orden'])
                        : null;

                    $precio = isset($orden['precio_final']) ? (float) $orden['precio_final'] : 0;
                @endphp

                <div class="servicio-card bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition"
                     data-estado="{{ $estado }}">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                        {{-- Info principal --}}
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            {{-- Icono del vehículo --}}
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center flex-shrink-0 shadow-sm">
                                <i class="fa-solid fa-car text-white text-xl"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                {{-- Folio + Estado --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs font-bold text-gray-400">
                                        {{ $orden['folio'] ?? '—' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $conf[0] }} {{ $conf[1] }} uppercase tracking-wider">
                                        <i class="fa-solid {{ $conf[2] }} text-[9px]"></i>
                                        {{ $conf[3] }}
                                    </span>
                                </div>

                                {{-- Vehículo --}}
                                <h3 class="font-bold text-gray-900 text-base mt-1.5">
                                    {{ $vehiculoDesc }}
                                </h3>

                                {{-- Taller + Placa --}}
                                <div class="flex items-center gap-3 mt-1 flex-wrap text-xs text-gray-500">
                                    @if ($placas)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-id-card text-gray-400"></i>
                                            {{ $placas }}
                                        </span>
                                    @endif
                                    @if (!empty($orden['taller_id']))
                                        <span class="text-gray-300">•</span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-store text-gray-400"></i>
                                            {{ $orden['taller_nombre'] ?? 'Taller' }}
                                        </span>
                                    @endif
                                    @if ($fecha)
                                        <span class="text-gray-300">•</span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-gray-400"></i>
                                            {{ $fecha->format('d M, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Precio + Acciones --}}
                        <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 flex-shrink-0 pl-0 sm:pl-4 border-t sm:border-t-0 sm:border-l border-gray-100 pt-4 sm:pt-0">
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Total</p>
                                <p class="text-lg font-black text-gray-900">
                                    ${{ number_format($precio, 2) }}
                                </p>
                            </div>

                            <a href="{{ route('cuenta.servicios.detalle', ['id' => $orden['id']]) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#0039A6] text-white text-xs font-semibold hover:bg-blue-800 transition whitespace-nowrap shadow-sm">
                                Ver detalle
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Barra de progreso --}}
                    @php
                        $progreso = match($estado) {
                            'Pendiente'     => 33,
                            'En reparación' => 66,
                            'Listo'         => 100,
                            'Cancelado'     => 0,
                            default         => 0,
                        };
                        $barColor = match($estado) {
                            'Pendiente'     => 'bg-yellow-400',
                            'En reparación' => 'bg-blue-500',
                            'Listo'         => 'bg-green-500',
                            'Cancelado'     => 'bg-red-400',
                            default         => 'bg-gray-300',
                        };
                    @endphp

                    @if ($estado !== 'Cancelado')
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">
                                <span class="{{ $estado === 'Pendiente' ? 'text-yellow-600' : '' }}">Recibido</span>
                                <span class="{{ $estado === 'En reparación' ? 'text-blue-600' : '' }}">En reparación</span>
                                <span class="{{ $estado === 'Listo' ? 'text-green-600' : '' }}">Listo</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $barColor }} rounded-full transition-all duration-700"
                                     style="width: {{ $progreso }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div id="sin-resultados-filtro" class="hidden bg-white rounded-3xl border border-dashed border-gray-200 p-10 text-center">
            <i class="fa-solid fa-filter-circle-xmark text-4xl text-gray-300"></i>
            <p class="text-sm text-gray-500 mt-3 font-medium">No hay servicios en esta categoría</p>
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const botones = document.querySelectorAll('.filtro-btn');
            const tarjetas = document.querySelectorAll('.servicio-card');
            const sinResultados = document.getElementById('sin-resultados-filtro');

            if (!botones.length) return;

            botones.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filtro = this.dataset.filtro;

                    // Estilos activos
                    botones.forEach(b => {
                        b.className = b.className.replace(/bg-\[#0039A6\] text-white/, 'bg-gray-100 text-gray-600 hover:bg-gray-200');
                    });
                    this.className = this.className.replace(/bg-gray-100 text-gray-600 hover:bg-gray-200/, 'bg-[#0039A6] text-white');

                    // Filtrar
                    let visibles = 0;
                    tarjetas.forEach(card => {
                        const estado = card.dataset.estado;
                        const mostrar = (filtro === 'todos') || (estado === filtro);
                        card.style.display = mostrar ? '' : 'none';
                        if (mostrar) visibles++;
                    });

                    if (sinResultados) {
                        sinResultados.classList.toggle('hidden', visibles > 0);
                    }
                });
            });
        });
    </script>
    @endpush

@endsection
