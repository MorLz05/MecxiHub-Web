@extends('GestorMaestro.layouts.app')

@section('title', 'Crear Usuario - MecxiHub')
@section('header-title', 'Crear Nuevo Usuario')
@section('header-subtitle', 'Agrega un nuevo usuario al sistema')

@section('content')
    <div class="mb-4">
        <a href="{{ route('gestor.usuarios') }}"
            class="inline-flex items-center gap-2 text-brand-blue hover:text-brand-darkblue transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la lista</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('gestor.usuarios.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nombre -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Nombre Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Ej: Juan Pérez"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-gray-800 placeholder-gray-400 bg-gray-50/50 shadow-inner transition">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Correo Electrónico <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    placeholder="ejemplo@correo.com"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-gray-800 placeholder-gray-400 bg-gray-50/50 shadow-inner transition">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contraseña -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-gray-800 placeholder-gray-400 bg-gray-50/50 shadow-inner transition">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="password_confirmation"
                    class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    placeholder="Repite la contraseña"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-gray-800 placeholder-gray-400 bg-gray-50/50 shadow-inner transition">
            </div>

            <!-- Rol -->
            <div>
                <label for="rol" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Rol <span class="text-red-500">*</span>
                </label>
                <select id="rol" name="rol" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-gray-800 bg-gray-50/50 shadow-inner transition">
                    <option value="">Selecciona un rol</option>
                    <option value="GestorMaestro" {{ old('rol') === 'GestorMaestro' ? 'selected' : '' }}>Gestor Maestro
                    </option>
                    <option value="Conductor" {{ old('rol') === 'Conductor' ? 'selected' : '' }}>Conductor</option>
                </select>
                @error('rol')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado Activo -->
            <div class="flex items-center gap-3 pt-2">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="activo" name="activo" value="1"
                        {{ old('activo', true) ? 'checked' : '' }} class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:bg-brand-blue relative">
                    </div>
                </label>
                <span class="text-sm font-medium text-gray-700">Usuario activo</span>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit"
                    class="px-6 py-3 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-save mr-2"></i>
                    Crear Usuario
                </button>
                <a href="{{ route('gestor.usuarios') }}"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
