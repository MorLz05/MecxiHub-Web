@extends('taller.layouts.app')

@section('title', 'Órdenes de Trabajo - MecxiHub')

@push('styles')
    <style>
        .estado-badge {
            font-size: 11px;
        }

        .table-row:hover {
            background-color: #f9fafb;
        }
    </style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                <span
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-blue to-brand-darkblue text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-clipboard-list text-lg"></i>
                </span>
                Órdenes de Trabajo
            </h1>
            <p class="text-sm text-gray-500 mt-2">Gestiona todas las órdenes de reparación de tu taller.</p>
        </div>

        <a href="{{ route('taller.ordenes.crear') }}"
            class="inline-flex items-center gap-2 px-5 py-3 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-orange-500/30 transition">
            <i class="fa-solid fa-plus"></i>
            Nueva Orden
        </a>
    </div>

    {{-- Mensajes --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    @if (!empty($sinTaller))
        <div
            class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i>
            <span class="text-sm">Tu usuario no está asociado a ningún taller. Contacta al administrador.</span>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('taller.ordenes') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search"
                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fa-solid fa-search"></i>
                    </span>
                    <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                        placeholder="Folio, cliente, placas, marca..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                </div>
            </div>

            <div class="min-w-[170px]">
                <label for="estado"
                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Estado</label>
                <select id="estado" name="estado"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                    <option value="">Todos</option>
                    @foreach (['Pendiente', 'En reparación', 'Listo', 'Cancelado'] as $est)
                        <option value="{{ $est }}" {{ ($filterEstado ?? '') === $est ? 'selected' : '' }}>
                            {{ $est }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-filter mr-2"></i> Filtrar
                </button>
                <a href="{{ route('taller.ordenes') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-rotate-right mr-2"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Contador --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <span class="font-semibold">{{ $total ?? 0 }}</span> órdenes encontradas
        </p>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Vehículo</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Fallas</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio
                            final</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ordenes ?? [] as $orden)
                        @php
                            $estado = $orden['estado'] ?? 'Pendiente';
                            $colores = [
                                'Pendiente' => 'bg-yellow-100 text-yellow-700',
                                'En reparación' => 'bg-blue-100 text-brand-blue',
                                'Listo' => 'bg-green-100 text-green-700',
                                'Cancelado' => 'bg-red-100 text-red-700',
                            ];
                            $colorBadge = $colores[$estado] ?? 'bg-gray-100 text-gray-600';
                            $vehiculo = $orden['vehiculo'] ?? [];
                            $fallas = $orden['fallas_multiples'] ?? ($orden['falla'] ?? []);
                            if (isset($orden['falla']) && !isset($orden['falla'][0]) && !empty($orden['falla'])) {
                                $fallas = [$orden['falla']];
                            }
                            $numFallas = is_array($fallas) ? count($fallas) : 0;
                            $moneda = $orden['presupuesto']['moneda'] ?? 'MXN';
                            $simbolos = ['USD' => '$', 'MXN' => '$', 'EUR' => '€'];
                            $simbolo = $simbolos[$moneda] ?? '$';
                        @endphp
                        <tr class="table-row transition">
                            <td class="px-6 py-4">
                                <span
                                    class="font-mono text-xs font-bold text-brand-blue bg-brand-blue/10 px-2.5 py-1 rounded-md">
                                    {{ $orden['folio'] ?? 'S/F' }}
                                </span>
                                <p class="text-[11px] text-gray-400 mt-1">
                                    {{ isset($orden['fecha_orden']) ? \Carbon\Carbon::parse($orden['fecha_orden'])->format('d/m/Y H:i') : '' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $orden['cliente_nombre'] ?? 'Sin nombre' }}</p>
                                @if (!empty($orden['cliente_telefono']))
                                    <p class="text-xs text-gray-500"><i
                                            class="fa-solid fa-phone text-[10px] mr-1"></i>{{ $orden['cliente_telefono'] }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ $vehiculo['marca'] ?? '' }} {{ $vehiculo['modelo'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $vehiculo['anio'] ?? '' }}
                                    @if (!empty($vehiculo['placas']))
                                        · {{ strtoupper($vehiculo['placas']) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                    <i class="fa-solid fa-screwdriver-wrench text-[10px]"></i>
                                    {{ $numFallas }} {{ $numFallas === 1 ? 'falla' : 'fallas' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-gray-900">
                                    {{ $simbolo }}{{ number_format($orden['precio_final'] ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colorBadge }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('taller.ordenes.show', $orden['id']) }}"
                                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-blue hover:text-white text-gray-600 flex items-center justify-center transition"
                                        title="Ver detalle">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    @if (!in_array($estado, ['Cancelado', 'Listo'], true))
                                        <a href="{{ route('taller.ordenes.editar', $orden['id']) }}"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-orange hover:text-white text-gray-600 flex items-center justify-center transition"
                                            title="Editar">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-clipboard-list text-4xl text-gray-300"></i>
                                    <p class="font-medium">No hay órdenes registradas</p>
                                    <p class="text-sm text-gray-400">Crea tu primera orden de trabajo</p>
                                    <a href="{{ route('taller.ordenes.crear') }}"
                                        class="mt-3 px-5 py-2 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition">
                                        <i class="fa-solid fa-plus mr-2"></i> Crear Orden
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if (isset($totalPages) && $totalPages > 1)
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-sm text-gray-600">Mostrando página {{ $page ?? 1 }} de {{ $totalPages }}</p>
                    <div class="flex gap-1">
                        @if (($page ?? 1) > 1)
                            <a href="{{ route('taller.ordenes', array_merge(request()->query(), ['page' => ($page ?? 1) - 1])) }}"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        @endif

                        @php
                            $currentPage = $page ?? 1;
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            <a href="{{ route('taller.ordenes', array_merge(request()->query(), ['page' => $i])) }}"
                                class="px-3 py-1.5 rounded-lg text-sm transition {{ $i == $currentPage ? 'bg-brand-blue text-white' : 'hover:bg-gray-100' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if (($page ?? 1) < $totalPages)
                            <a href="{{ route('taller.ordenes', array_merge(request()->query(), ['page' => ($page ?? 1) + 1])) }}"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
