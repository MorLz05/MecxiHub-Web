@extends('conductor.layouts.app')

@section('title', 'Error en el servidor - MecxiHub')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">

            <!-- Ícono animado -->
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fa-solid fa-plug-circle-xmark text-5xl text-red-500"></i>
            </div>

            <!-- Título -->
            <h1 class="text-3xl font-black mb-2">
                <span class="text-[#0039A6]">Error</span> <span class="text-[#FF6B00]">en el Servidor</span>
            </h1>

            <p class="text-gray-600 text-sm mb-8 leading-relaxed">
                Parece que hemos perdido la conexión con nuestros servicios. Esto puede ser debido a un problema con tu red de internet o porque nuestros servidores están en mantenimiento.
            </p>

            <!-- Caja de pasos a seguir -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 mb-8 text-left">
                <p class="text-sm font-semibold text-gray-700 mb-3">¿Qué puedes hacer?</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-wifi text-[#0039A6] mt-0.5"></i>
                        <span>Verifica que tu conexión a internet siga activa.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-rotate-right text-[#0039A6] mt-0.5"></i>
                        <span>Recarga la página en unos momentos.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-headset text-[#0039A6] mt-0.5"></i>
                        <span>Si el problema persiste, contacta a soporte.</span>
                    </li>
                </ul>
            </div>

            <!-- Botón de regreso -->
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl bg-[#0039A6] hover:bg-[#002B80] text-white font-semibold text-base shadow-lg shadow-blue-900/20 transition">
                <i class="fa-solid fa-arrow-rotate-right"></i>
                Reintentar conexión
            </a>
        </div>
    </div>
@endsection
