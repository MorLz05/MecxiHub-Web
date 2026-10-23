@extends('conductor.layouts.app')

@section('title', 'Página no encontrada - MecxiHub')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">

            <!-- Ícono -->
            <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fa-solid fa-map-location-dot text-5xl text-[#FF6B00]"></i>
            </div>

            <!-- Título -->
            <h1 class="text-5xl font-black mb-2 text-gray-900">404</h1>
            <h2 class="text-2xl font-bold mb-4">
                <span class="text-[#0039A6]">Página</span> <span class="text-[#FF6B00]">no encontrada</span>
            </h2>

            <p class="text-gray-600 text-sm mb-8 leading-relaxed">
                Lo sentimos, la página que estás buscando no existe, ha sido movida o ya no está disponible. Verifica la URL o regresa al inicio.
            </p>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('home') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-[#0039A6] hover:bg-[#002B80] text-white font-semibold text-base shadow-lg shadow-blue-900/20 transition">
                    <i class="fa-solid fa-house"></i>
                    Ir al Inicio
                </a>
                <a href="{{ route('buscar.talleres') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl border-2 border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-base transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Buscar Talleres
                </a>
            </div>
        </div>
    </div>
@endsection
