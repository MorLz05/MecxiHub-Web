{{-- 1. Extendemos el layout base --}}
@extends('conductor.layouts.app')

{{-- 2. Definimos el título de esta página específica (opcional) --}}
@section('title', 'Inicio - MecxiHub')

{{-- 3. Definimos el contenido principal --}}
@section('content')
    <!-- HERO SECTION -->
    <section class="relative bg-[#002677] text-white pt-10 pb-32 px-4 sm:px-8 overflow-hidden">

        <!-- === RELIEVES Y CAPAS SUPERPUESTAS (BACKGROUND SHAPES) === -->

        <!-- 1. Relieve Azul Curvo Traslapado (Ondas de fondo) -->
        <svg class="absolute top-0 left-0 w-full h-full pointer-events-none z-0" viewBox="0 0 1440 480" fill="none" preserveAspectRatio="none">
            <!-- Onda Azul Vibrante traslapada -->
            <path d="M-100,-20 C200,100 350,300 100,500 L-100,500 Z" fill="url(#blue-grad-1)" opacity="0.7" />
            <!-- Onda Azul Clarito de acento -->
            <path d="M-50,-50 C300,50 400,250 200,480 L-100,480 Z" fill="url(#blue-grad-2)" opacity="0.3" />

            <defs>
                <linearGradient id="blue-grad-1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0066FF" />
                    <stop offset="100%" stop-color="#001B5E" />
                </linearGradient>
                <linearGradient id="blue-grad-2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#2979FF" />
                    <stop offset="100%" stop-color="#0039A6" />
                </linearGradient>
            </defs>
        </svg>

        <!-- 2. Imagen de Fondo del Vehículo -->
        <div class="absolute inset-y-0 right-0 w-full lg:w-3/5 pointer-events-none z-0">
            <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80"
                 alt="Auto en taller"
                 class="w-full h-full object-cover object-center opacity-75 sm:opacity-85 [mask-image:linear-gradient(to_right,transparent_0%,black_30%,black_70%,transparent_100%)]">
        </div>

        <!-- 3. Onda Curva Naranja (Borde derecho limpio, sin círculos ni resplandores) -->
        <svg class="absolute top-0 right-0 h-full w-28 sm:w-48 lg:w-64 pointer-events-none z-0 text-[#FF6B00]" viewBox="0 0 200 500" fill="none" preserveAspectRatio="none">
            <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.85" />
            <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.4" />
        </svg>

        <!-- === CONTENIDO DEL HERO === -->
        <div class="max-w-7xl mx-auto relative z-10 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                <!-- Columna Izquierda: Información -->
                <div class="lg:col-span-8 space-y-8">

                    <!-- Header con Logo2 y Título Principal -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <!-- Logo2 -->
                        <div class="flex-shrink-0 pr-6 sm:border-r sm:border-white/20">
                            <img src="{{ asset('images/logo/logo2.png') }}" alt="MXH MecxiHub" class="h-16 sm:h-20 w-auto object-contain">
                        </div>

                        <!-- Título Principal -->
                        <div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                                Tu auto, en las <br>
                                <span class="text-[#FF6B00]">mejores manos</span>
                            </h2>
                            <p class="text-blue-100/90 text-sm sm:text-base mt-2 max-w-md font-normal">
                                Describe el problema de tu vehículo y encuentra el taller ideal con ayuda de nuestra IA.
                            </p>
                        </div>
                    </div>

                    <!-- Lista de Características / Ventajas -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-shield-halved text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Talleres verificados<br>y calificados</span>
                        </div>

                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-robot text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Diagnóstico inteligente<br>con IA</span>
                        </div>

                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-star text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Opiniones reales<br>de clientes</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- MAIN INTERACTIVE CARD (PROMPT IA) -->
    <div class="max-w-6xl mx-auto px-4 -mt-20 relative z-20">
        <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border border-gray-100">
            <!-- Header section -->
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                    <h3 class="text-2xl font-bold text-gray-900">¿Qué problema tiene tu vehículo?</h3>
                </div>
                <p class="text-gray-500 text-sm mt-1 ml-4">Cuéntanos la falla y te ayudaremos a encontrar el taller ideal.</p>
            </div>

            <!-- Category Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <button type="button"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-medium hover:border-[#0039A6] hover:text-[#0039A6] transition bg-white shadow-sm">
                    <i class="fa-solid fa-engine text-lg"></i> Motor
                </button>
                <button type="button"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#FF6B00] text-white font-semibold shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-compact-disc text-lg"></i> Frenos
                </button>
                <button type="button"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-medium hover:border-[#0039A6] hover:text-[#0039A6] transition bg-white shadow-sm">
                    <i class="fa-solid fa-car-battery text-lg"></i> Sistema eléctrico
                </button>
                <button type="button"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-medium hover:border-[#0039A6] hover:text-[#0039A6] transition bg-white shadow-sm">
                    <i class="fa-solid fa-dharmachakra text-lg"></i> Suspensión
                </button>
            </div>

            <!-- Input Bar -->
            <form action="#" method="POST" class="space-y-5">
                @csrf
                <div class="relative flex items-center">
                    <input type="text" name="prompt"
                        placeholder="Ej. Mi auto vibra al frenar, hace ruido o el pedal se va al fondo..."
                        class="w-full pl-5 pr-14 py-4 rounded-2xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-700 placeholder-gray-400 text-sm bg-gray-50/50 shadow-inner">
                    <button type="button"
                        class="absolute right-3 p-2.5 text-[#FF6B00] hover:bg-orange-50 rounded-xl transition">
                        <i class="fa-solid fa-microphone text-xl"></i>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="inline-flex items-center gap-2.5 bg-[#0039A6] hover:bg-[#002B80] text-white px-7 py-3.5 rounded-xl font-semibold shadow-lg shadow-blue-900/20 transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-base"></i>
                    Consultar con IA
                </button>
            </form>
        </div>
    </div>

    <!-- TALLERES RECOMENDADOS SECTION -->
    <section class="max-w-6xl mx-auto px-4 py-14">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-star text-[#FF6B00] text-xl"></i>
                <h3 class="text-xl font-bold text-gray-900">Talleres recomendados para ti</h3>
            </div>
            <a href="#" class="text-[#0039A6] font-semibold text-sm hover:underline flex items-center gap-1">
                Ver todos <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
        </div>

        <!-- Cards Container -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @php
                $talleres = [
                    [
                        'nombre' => 'Taller López',
                        'especialidad' => 'Frenos, Suspensión',
                        'rating' => '4.8',
                        'reviews' => '128',
                        'distancia' => '1.2 km de ti',
                        'imagen' => 'https://images.unsplash.com/photo-1613214149922-f1809c99b414?auto=format&fit=crop&w=500&q=80',
                    ],
                    [
                        'nombre' => 'Mecánica Express',
                        'especialidad' => 'Frenos, Afinación',
                        'rating' => '4.6',
                        'reviews' => '96',
                        'distancia' => '1.8 km de ti',
                        'imagen' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=500&q=80',
                    ],
                    [
                        'nombre' => 'AutoFix',
                        'especialidad' => 'Frenos, Motor',
                        'rating' => '4.7',
                        'reviews' => '83',
                        'distancia' => '2.3 km de ti',
                        'imagen' => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?auto=format&fit=crop&w=500&q=80',
                    ],
                ];
            @endphp

            @foreach ($talleres as $taller)
                <div class="bg-white rounded-2xl border border-blue-200/80 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex gap-4 items-start">
                            <!-- Image -->
                            <img src="{{ $taller['imagen'] }}" alt="{{ $taller['nombre'] }}"
                                class="w-24 h-24 rounded-xl object-cover flex-shrink-0">

                            <!-- Content -->
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <h4 class="font-bold text-gray-900 text-base">{{ $taller['nombre'] }}</h4>
                                    <i class="fa-solid fa-circle-check text-[#0039A6] text-sm"></i>
                                </div>
                                <p class="text-xs text-gray-500">Especialidad: {{ $taller['especialidad'] }}</p>

                                <!-- Rating -->
                                <div class="flex items-center gap-1 text-xs pt-1">
                                    <span class="font-bold text-gray-800">{{ $taller['rating'] }}</span>
                                    <div class="text-[#FF6B00] flex text-[10px] gap-0.5">
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <span class="text-gray-400">({{ $taller['reviews'] }})</span>
                                </div>

                                <!-- Distance -->
                                <p class="text-xs text-gray-500 flex items-center gap-1 pt-1">
                                    <i class="fa-solid fa-location-dot text-gray-400"></i> A {{ $taller['distancia'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 mt-5">
                        <a href="#"
                            class="text-center py-2.5 px-3 rounded-xl bg-blue-50 text-[#0039A6] font-semibold text-xs hover:bg-blue-100 transition">
                            Ver perfil
                        </a>
                        <a href="#"
                            class="text-center py-2.5 px-3 rounded-xl bg-[#FF6B00] text-white font-semibold text-xs hover:bg-orange-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
@endsection
