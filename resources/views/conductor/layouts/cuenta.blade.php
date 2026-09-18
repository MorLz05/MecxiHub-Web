@extends('conductor.layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Título de página -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Mi cuenta</h1>
                <p class="text-gray-500 text-sm mt-1">Administra tu información, vehículos y preferencias de cuenta.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <!-- === SIDEBAR - Tabs === -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-[#0039A6]/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-user text-[#0039A6] text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ session('firebase_user.nombre_completo') }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ session('firebase_user.email') }}</p>
                                </div>
                            </div>
                        </div>

                        <nav class="p-2 space-y-1">
                            <a href="{{ route('cuenta.resumen') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.resumen') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-chart-simple text-base"></i>
                                Resumen de cuenta
                            </a>
                            <a href="{{ route('cuenta.informacion-personal') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.informacion-personal') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-user-pen text-base"></i>
                                Información personal
                            </a>
                            <a href="{{ route('cuenta.servicios.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
    {{ request()->routeIs('cuenta.servicios*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-wrench text-base"></i>
                                Mis servicios
                            </a>
                            {{-- <a href="{{ route('cuenta.vehiculos') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.vehiculos*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-car text-base"></i>
                                Mis vehículos
                            </a> --}}
                            <a href="{{ route('cuenta.notificaciones.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
    {{ request()->routeIs('cuenta.notificaciones*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-bell text-base"></i>
                                Notificaciones
                                @if (($notifNoLeidas ?? 0) > 0)
                                    <span
                                        class="ml-auto bg-[#FF6B00] text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $notifNoLeidas > 9 ? '9+' : $notifNoLeidas }}
                                    </span>
                                @endif
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 hover:text-[#0039A6] font-medium text-sm transition">
                                <i class="fa-solid fa-crown text-base"></i>
                                Planes
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-red-600 hover:bg-red-50 font-medium text-sm transition w-full">
                                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>

                <!-- === CONTENIDO PRINCIPAL === -->
                <div class="lg:col-span-3 space-y-6">
                    @yield('cuenta-content')
                </div>
            </div>
        </div>
    </div>
@endsection
