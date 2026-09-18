@extends('GestorMaestro.layouts.app')

@section('title', 'Gestión de Planes - MecxiHub')
@section('header-title', 'Gestión de Planes')
@section('header-subtitle', 'Administra los planes disponibles en la plataforma')

@section('content')
    <!-- Mensajes -->
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Barra de búsqueda y filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <form action="{{ route('gestor.planes') }}" method="GET" class="flex-1 flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Buscar plan
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa-solid fa-search"></i>
                        </span>
                        <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                            placeholder="Nombre, descripción, característica..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                    </div>
                </div>

                <div class="min-w-[150px]">
                    <label for="estado" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Estado
                    </label>
                    <select id="estado" name="estado"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                        <option value="">Todos</option>
                        <option value="activo" {{ ($filterEstado ?? '') === 'activo' ? 'selected' : '' }}>Activos</option>
                        <option value="inactivo" {{ ($filterEstado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivos
                        </option>
                    </select>
                </div>

                <div class="min-w-[170px]">
                    <label for="tipo_usuario"
                        class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Tipo de usuario
                    </label>
                    <select id="tipo_usuario" name="tipo_usuario"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                        <option value="">Todos</option>
                        @foreach (['Conductor', 'Administrador', 'Taller', 'GestorMaestro'] as $tipo)
                            <option value="{{ $tipo }}" {{ ($filterTipo ?? '') === $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-filter mr-2"></i> Filtrar
                    </button>
                    <a href="{{ route('gestor.planes') }}"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-rotate-right mr-2"></i> Limpiar
                    </a>
                </div>
            </form>

            <a href="{{ route('gestor.planes.crear') }}"
                class="px-6 py-2.5 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-2"></i> Nuevo Plan
            </a>
        </div>
    </div>

    <!-- Contador -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <span class="font-semibold">{{ $total ?? 0 }}</span> planes encontrados
        </p>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Dirigido a</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Duración</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Características</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($planesPaginados ?? [] as $plan)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-lg bg-brand-orange/10 text-brand-orange flex items-center justify-center">
                                        <i class="fa-solid fa-crown text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 flex items-center gap-2">
                                            {{ $plan['nombre'] ?? 'Sin nombre' }}
                                            @if (!empty($plan['destacado']))
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase">
                                                    <i class="fa-solid fa-star text-[8px] mr-1"></i>Destacado
                                                </span>
                                            @endif
                                        </p>
                                        @if (!empty($plan['descripcion']))
                                            <p class="text-xs text-gray-500 line-clamp-1 max-w-xs">
                                                {{ \Illuminate\Support\Str::limit($plan['descripcion'], 60) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">
                                    {{ $plan['moneda'] ?? 'USD' }} {{ number_format($plan['precio'] ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $tipos = $plan['tipos_usuario'] ?? [];
                                    $coloresTipo = [
                                        'Conductor' => 'bg-blue-100 text-brand-blue',
                                        'Administrador' => 'bg-purple-100 text-purple-700',
                                        'Taller' => 'bg-green-100 text-green-700',
                                        'GestorMaestro' => 'bg-orange-100 text-brand-orange',
                                    ];
                                @endphp
                                @if (!empty($tipos) && is_array($tipos))
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach ($tipos as $tipo)
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $coloresTipo[$tipo] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ $tipo }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-brand-blue">
                                    {{ $plan['duracion_dias'] ?? 0 }} días
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if (!empty($plan['caracteristicas']) && is_array($plan['caracteristicas']))
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach (array_slice($plan['caracteristicas'], 0, 2) as $car)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600">
                                                {{ \Illuminate\Support\Str::limit($car, 20) }}
                                            </span>
                                        @endforeach
                                        @if (count($plan['caracteristicas']) > 2)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-500">
                                                +{{ count($plan['caracteristicas']) - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Sin características</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer toggle-activo" data-id="{{ $plan['id'] }}"
                                        {{ isset($plan['activo']) && $plan['activo'] === true ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                                peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                                peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                                after:transition-all peer-checked:bg-brand-blue relative">
                                    </div>
                                </label>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('gestor.planes.editar', $plan['id']) }}"
                                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-blue hover:text-white text-gray-600 flex items-center justify-center transition"
                                        title="Editar">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <button type="button"
                                        class="btn-eliminar w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-500 hover:text-white text-gray-600 flex items-center justify-center transition"
                                        data-id="{{ $plan['id'] }}" data-nombre="{{ $plan['nombre'] ?? 'plan' }}"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-crown text-4xl text-gray-300"></i>
                                    <p class="font-medium">No hay planes disponibles</p>
                                    <p class="text-sm text-gray-400">Crea tu primer plan para comenzar</p>
                                    <a href="{{ route('gestor.planes.crear') }}"
                                        class="mt-3 px-5 py-2 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition">
                                        <i class="fa-solid fa-plus mr-2"></i> Crear Plan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if (isset($totalPages) && $totalPages > 1)
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-sm text-gray-600">
                        Mostrando página {{ $page ?? 1 }} de {{ $totalPages }}
                    </p>
                    <div class="flex gap-1">
                        @if (($page ?? 1) > 1)
                            <a href="{{ route('gestor.planes', array_merge(request()->query(), ['page' => ($page ?? 1) - 1])) }}"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        @endif

                        @php
                            $currentPage = $page ?? 1;
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                        @endphp

                        @if ($start > 1)
                            <a href="{{ route('gestor.planes', array_merge(request()->query(), ['page' => 1])) }}"
                                class="px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm transition">1</a>
                            @if ($start > 2)
                                <span class="px-3 py-1.5 text-gray-400">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <a href="{{ route('gestor.planes', array_merge(request()->query(), ['page' => $i])) }}"
                                class="px-3 py-1.5 rounded-lg text-sm transition {{ $i == $currentPage ? 'bg-brand-blue text-white' : 'hover:bg-gray-100' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if ($end < $totalPages)
                            @if ($end < $totalPages - 1)
                                <span class="px-3 py-1.5 text-gray-400">...</span>
                            @endif
                            <a href="{{ route('gestor.planes', array_merge(request()->query(), ['page' => $totalPages])) }}"
                                class="px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm transition">{{ $totalPages }}</a>
                        @endif

                        @if (($page ?? 1) < $totalPages)
                            <a href="{{ route('gestor.planes', array_merge(request()->query(), ['page' => ($page ?? 1) + 1])) }}"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Toggle activo
                document.querySelectorAll('.toggle-activo').forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const id = this.dataset.id;
                        const isChecked = this.checked;
                        const el = this;
                        el.disabled = true;

                        fetch(`/gestor/plan/${id}/toggle-activo`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    activo: isChecked
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast(data.message, 'success');
                                } else {
                                    el.checked = !isChecked;
                                    showToast(data.error || 'Error', 'error');
                                }
                            })
                            .catch(() => {
                                el.checked = !isChecked;
                                showToast('Error al actualizar', 'error');
                            })
                            .finally(() => {
                                el.disabled = false;
                            });
                    });
                });

                // Eliminar
                document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const nombre = this.dataset.nombre;

                        if (!confirm(
                                `¿Eliminar el plan "${nombre}"? Esta acción no se puede deshacer.`))
                            return;

                        fetch(`/gestor/plan/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast(data.message, 'success');
                                    setTimeout(() => location.reload(), 800);
                                } else {
                                    showToast(data.error || 'Error', 'error');
                                }
                            })
                            .catch(() => showToast('Error al eliminar', 'error'));
                    });
                });

                function showToast(message, type) {
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 ${
                        type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'
                    }`;
                    toast.innerHTML = `
                        <i class="fa-solid ${type === 'success' ? 'fa-circle-check text-green-500' : 'fa-circle-exclamation text-red-500'}"></i>
                        <span>${message}</span>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }, 3000);
                }
            });
        </script>
    @endpush
@endsection
