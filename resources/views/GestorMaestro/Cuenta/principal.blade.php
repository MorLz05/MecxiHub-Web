@extends('GestorMaestro.layouts.app')

@section('title', 'Mi Cuenta - Gestor MecxiHub')
@section('header-title', 'Mi Cuenta')
@section('header-subtitle', 'Gestiona tu información personal')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Panel de Gestor</h2>
                <p class="text-sm text-gray-500">Bienvenido al panel de administración</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
            <!-- Tarjeta de información -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-brand-blue flex items-center justify-center">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nombre</p>
                        <p class="font-semibold text-gray-900">{{ session('firebase_user.nombre_completo', 'Gestor') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-semibold text-gray-900">{{ session('firebase_user.email', 'gestor@mecxihub.com') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Rol</p>
                        <p class="font-semibold text-gray-900">{{ session('firebase_user.rol', 'GestorMaestro') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje de bienvenida -->
        <div class="mt-8 p-4 bg-brand-blue/5 rounded-xl border border-brand-blue/20">
            <p class="text-sm text-gray-700">
                <i class="fa-solid fa-info-circle text-brand-blue mr-2"></i>
                Este es tu panel de gestor. Desde aquí puedes administrar talleres, usuarios y configurar tu cuenta.
            </p>
        </div>
    </div>
@endsection
