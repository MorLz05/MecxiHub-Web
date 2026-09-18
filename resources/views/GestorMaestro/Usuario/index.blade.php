@extends('GestorMaestro.layouts.app')

@section('title', 'Gestión de Usuarios - MecxiHub')
@section('header-title', 'Gestión de Usuarios')
@section('header-subtitle', 'Administra todos los usuarios de la plataforma')

@section('content')
    <!-- Mensajes de éxito/error -->
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
            <form action="{{ route('gestor.usuarios') }}" method="GET" class="flex-1 flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Buscar usuario
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa-solid fa-search"></i>
                        </span>
                        <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                            placeholder="Nombre, email, usuario..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                    </div>
                </div>

                <div class="min-w-[150px]">
                    <label for="rol" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Rol
                    </label>
                    <select id="rol" name="rol"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                        <option value="">Todos</option>
                        <option value="GestorMaestro" {{ ($filterRol ?? '') === 'GestorMaestro' ? 'selected' : '' }}>Gestor
                            Maestro</option>
                        <option value="Conductor" {{ ($filterRol ?? '') === 'Conductor' ? 'selected' : '' }}>Conductor
                        </option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-filter mr-2"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('gestor.usuarios') }}"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                        <i class="fa-solid fa-rotate-right mr-2"></i>
                        Limpiar
                    </a>
                </div>
            </form>

            <a href="{{ route('gestor.usuarios.crear') }}"
                class="px-6 py-2.5 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-2"></i>
                Nuevo Usuario
            </a>
        </div>
    </div>

    <!-- Contador de resultados -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <span class="font-semibold">{{ $total ?? 0 }}</span> usuarios encontrados
        </p>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Email
                        </th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol
                        </th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($usuariosPaginados ?? [] as $usuario)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($usuario['nombre_completo'] ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="font-mono text-xs text-gray-600">{{ $usuario['usuario'] ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $usuario['nombre_completo'] ?? 'Sin nombre' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $usuario['email'] ?? 'Sin email' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ ($usuario['rol'] ?? '') === 'GestorMaestro' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-brand-blue' }}">
                                    {{ $usuario['rol'] ?? 'Conductor' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer toggle-activo"
                                        data-uid="{{ $usuario['uid'] }}"
                                        {{ isset($usuario['activo']) && $usuario['activo'] === true ? 'checked' : '' }}
                                        @if (($usuario['uid'] ?? '') === session('firebase_user.uid')) disabled @endif>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                                peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                                peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                                after:transition-all peer-checked:bg-brand-blue relative
                                                {{ ($usuario['uid'] ?? '') === session('firebase_user.uid') ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    </div>
                                </label>
                                @if (($usuario['uid'] ?? '') === session('firebase_user.uid'))
                                    <span class="text-xs text-gray-400 ml-1">(Tú)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-users text-4xl text-gray-300"></i>
                                    <p class="font-medium">No se encontraron usuarios</p>
                                    <p class="text-sm text-gray-400">Prueba ajustando los filtros de búsqueda</p>
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
                            <a href="{{ route('gestor.usuarios', array_merge(request()->query(), ['page' => ($page ?? 1) - 1])) }}"
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
                            <a href="{{ route('gestor.usuarios', array_merge(request()->query(), ['page' => 1])) }}"
                                class="px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm transition">1</a>
                            @if ($start > 2)
                                <span class="px-3 py-1.5 text-gray-400">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <a href="{{ route('gestor.usuarios', array_merge(request()->query(), ['page' => $i])) }}"
                                class="px-3 py-1.5 rounded-lg text-sm transition {{ $i == $currentPage ? 'bg-brand-blue text-white' : 'hover:bg-gray-100' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if ($end < $totalPages)
                            @if ($end < $totalPages - 1)
                                <span class="px-3 py-1.5 text-gray-400">...</span>
                            @endif
                            <a href="{{ route('gestor.usuarios', array_merge(request()->query(), ['page' => $totalPages])) }}"
                                class="px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm transition">{{ $totalPages }}</a>
                        @endif

                        @if (($page ?? 1) < $totalPages)
                            <a href="{{ route('gestor.usuarios', array_merge(request()->query(), ['page' => ($page ?? 1) + 1])) }}"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Script para el toggle de activo -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const toggles = document.querySelectorAll('.toggle-activo');

                toggles.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const uid = this.dataset.uid;
                        const isChecked = this.checked;
                        const toggleElement = this;

                        // Mostrar loading
                        toggleElement.disabled = true;

                        // Construir la URL
                        const url = `/gestor/usuario/${uid}/toggle-activo`;

                        // Enviar petición AJAX
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    activo: isChecked
                                })
                            })
                            .then(response => {
                                console.log(`📥 Respuesta recibida: ${response.status}`);
                                return response.json();
                            })
                            .then(data => {

                                if (data.success) {
                                    showToast(data.message, 'success');
                                } else {
                                    toggleElement.checked = !isChecked;
                                    showToast(data.error || 'Error al actualizar', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('❌ Error en fetch:', error);
                                toggleElement.checked = !isChecked;
                                showToast('Error al actualizar el estado', 'error');
                            })
                            .finally(() => {
                                toggleElement.disabled = false;
                            });
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
