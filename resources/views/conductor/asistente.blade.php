{{-- 1. Extendemos el layout base --}}
@extends('conductor.layouts.app')

{{-- 2. Definimos el título de esta página específica --}}
@section('title', 'Asistente IA - MecxiHub')

{{-- 3. Definimos el contenido principal --}}
@section('content')
    <!-- HERO SECTION -->
    <section class="relative bg-[#002677] text-white pt-10 pb-32 px-4 sm:px-8 overflow-hidden">

        <!-- Background Shapes -->
        <svg class="absolute top-0 left-0 w-full h-full pointer-events-none z-0" viewBox="0 0 1440 480" fill="none" preserveAspectRatio="none">
            <path d="M-100,-20 C200,100 350,300 100,500 L-100,500 Z" fill="url(#blue-grad-1)" opacity="0.7" />
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

        <!-- Imagen de Fondo -->
        <div class="absolute inset-y-0 right-0 w-full lg:w-3/5 pointer-events-none z-0">
            <img src="https://images.unsplash.com/photo-1535378917042-10a22c95931a?auto=format&fit=crop&w=1200&q=80"
                 alt="Asistente IA"
                 class="w-full h-full object-cover object-center opacity-75 sm:opacity-85 [mask-image:linear-gradient(to_right,transparent_0%,black_30%,black_70%,transparent_100%)]">
        </div>

        <!-- Onda Curva Naranja -->
        <svg class="absolute top-0 right-0 h-full w-28 sm:w-48 lg:w-64 pointer-events-none z-0 text-[#FF6B00]" viewBox="0 0 200 500" fill="none" preserveAspectRatio="none">
            <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.85" />
            <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.4" />
        </svg>

        <!-- Contenido Hero -->
        <div class="max-w-7xl mx-auto relative z-10 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-8">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <div class="flex-shrink-0 pr-6 sm:border-r sm:border-white/20">
                            <img src="{{ asset('images/logo/logo2.png') }}" alt="MXH MecxiHub" class="h-16 sm:h-20 w-auto object-contain">
                        </div>
                        <div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                                Asistente IA <br>
                                <span class="text-[#FF6B00]">MexciBot</span>
                            </h2>
                            <p class="text-blue-100/90 text-sm sm:text-base mt-2 max-w-md font-normal">
                                Describe el problema de tu vehículo y recibe ayuda personalizada al instante.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-bolt text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Respuestas<br>inmediatas</span>
                        </div>

                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-brain text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Diagnóstico<br>inteligente</span>
                        </div>

                        <div class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-circle-check text-[#FF6B00] text-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium leading-tight">Recomendaciones<br>confiables</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CHAT + SIDEBAR (Misma línea) -->
    <div class="max-w-7xl mx-auto px-4 -mt-12 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Columna Principal: Chat -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border border-gray-100 h-full flex flex-col">
                    <!-- Header Chat -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                            <h3 class="text-2xl font-bold text-gray-900">Chat con MexciBot</h3>
                            <span class="ml-2 inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-0.5 rounded-full text-xs font-semibold">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                En línea
                            </span>
                        </div>
                        <p class="text-gray-500 text-sm mt-1 ml-4">Nuestro asistente IA está aquí para ayudarte.</p>
                    </div>

                    <!-- Conversación -->
                    <div class="space-y-6 flex-1 max-h-[500px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">

                        <!-- Mensaje Usuario -->
                        <div class="flex justify-end">
                            <div class="bg-[#0039A6] text-white rounded-2xl rounded-tr-sm px-5 py-3 max-w-[75%] shadow-md">
                                <p class="text-sm leading-relaxed">Mi auto vibra al frenar y hace un ruido como rechilado.</p>
                                <span class="text-[10px] text-blue-200/80 mt-1 block text-right">Hoy 10:30 AM</span>
                            </div>
                        </div>

                        <!-- Mensaje MexciBot -->
                        <div class="flex justify-start">
                            <div class="bg-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-5 py-3 max-w-[85%] shadow-sm border border-gray-200/50">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-[#FF6B00] font-bold text-xs">🤖 MexciBot</span>
                                </div>
                                <p class="text-sm leading-relaxed">
                                    Entiendo el problema que describes. Con base en los síntomas que mencionas, estas pueden ser las causas más comunes:
                                </p>
                                <ul class="mt-2 space-y-1 text-sm">
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#FF6B00] font-bold">•</span>
                                        Pastillas de freno desgastadas
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#FF6B00] font-bold">•</span>
                                        Discos de freno dañados o deformados
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#FF6B00] font-bold">•</span>
                                        Suciedad o piedras entre las pastillas y el disco
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#FF6B00] font-bold">•</span>
                                        Mordazas de freno con mal funcionamiento
                                    </li>
                                </ul>
                                <span class="text-[10px] text-gray-400 mt-2 block">Hoy 10:30 AM</span>
                            </div>
                        </div>

                        <!-- Mensaje Usuario -->
                        <div class="flex justify-end">
                            <div class="bg-[#0039A6] text-white rounded-2xl rounded-tr-sm px-5 py-3 max-w-[75%] shadow-md">
                                <p class="text-sm leading-relaxed">¿Es peligroso seguir manejando así?</p>
                                <span class="text-[10px] text-blue-200/80 mt-1 block text-right">Hoy 10:31 AM</span>
                            </div>
                        </div>

                        <!-- Mensaje MexciBot -->
                        <div class="flex justify-start">
                            <div class="bg-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-5 py-3 max-w-[85%] shadow-sm border border-gray-200/50">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-[#FF6B00] font-bold text-xs">🤖 MexciBot</span>
                                </div>
                                <p class="text-sm leading-relaxed">
                                    <span class="font-semibold text-red-600">Sí, puede ser peligroso.</span> Si las pastillas están muy desgastadas o los discos dañados, tu sistema de frenos puede fallar, aumentando la distancia de frenado y el riesgo de accidente. Te recomiendo revisarlo lo antes posible.
                                </p>
                                <span class="text-[10px] text-gray-400 mt-2 block">Hoy 10:31 AM</span>
                            </div>
                        </div>

                        <!-- Separador de mensajes antiguos -->
                        <div class="flex items-center gap-3 py-2">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium">Hoy</span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                    </div>

                    <!-- Input de mensaje -->
                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 relative">
                                <input type="text"
                                       placeholder="Escribe tu mensaje aquí..."
                                       class="w-full pl-5 pr-12 py-3.5 rounded-2xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-700 placeholder-gray-400 text-sm bg-gray-50/50 transition">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF6B00] transition p-1.5">
                                    <i class="fa-solid fa-paperclip text-sm"></i>
                                </button>
                            </div>
                            <button type="button" class="bg-[#FF6B00] text-white p-3.5 rounded-2xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30 flex-shrink-0">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 text-center">MexciBot puede cometer errores. Verifica la información importante.</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Posibles Causas + Talleres Recomendados -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Posibles Causas -->
                <div class="bg-white rounded-2xl border border-blue-200/80 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <i class="fa-solid fa-list-check text-[#FF6B00] text-lg"></i>
                        <h4 class="font-bold text-gray-900">Posibles causas</h4>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-orange-50 border border-orange-100/80">
                            <span class="w-5 h-5 rounded-full bg-[#FF6B00]/20 text-[#FF6B00] flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</span>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Pastillas de freno desgastadas</p>
                                <p class="text-[10px] text-gray-500">Fricción excesiva por desgaste</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-blue-50 border border-blue-100/80">
                            <span class="w-5 h-5 rounded-full bg-[#0039A6]/20 text-[#0039A6] flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Discos de freno dañados</p>
                                <p class="text-[10px] text-gray-500">Deformados o con ranuras</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-blue-50 border border-blue-100/80">
                            <span class="w-5 h-5 rounded-full bg-[#0039A6]/20 text-[#0039A6] flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Suciedad entre piezas</p>
                                <p class="text-[10px] text-gray-500">Piedras o residuos atrapados</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-blue-50 border border-blue-100/80">
                            <span class="w-5 h-5 rounded-full bg-[#0039A6]/20 text-[#0039A6] flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</span>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Mordazas defectuosas</p>
                                <p class="text-[10px] text-gray-500">Mal funcionamiento del sistema</p>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="mt-3 inline-flex items-center gap-1 text-[#0039A6] font-semibold text-xs hover:underline">
                        Ver más información
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Talleres Recomendados -->
                <div class="bg-white rounded-2xl border border-blue-200/80 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <i class="fa-solid fa-star text-[#FF6B00] text-lg"></i>
                        <h4 class="font-bold text-gray-900">Talleres recomendados</h4>
                        <span class="ml-auto text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-medium">2</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Taller 1 -->
                        <div class="p-3 rounded-xl border border-gray-100 hover:border-blue-200 transition bg-gray-50/50">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0039A6] to-[#002677] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    TL
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h5 class="font-bold text-sm text-gray-900 truncate">Taller López</h5>
                                        <span class="text-[10px] bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full">🏆</span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="font-bold text-gray-800 text-xs">4.8</span>
                                        <div class="text-[#FF6B00] flex text-[8px] gap-0.5">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                        <span class="text-gray-400 text-[10px]">(128)</span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-gray-400"></i> 1.2 km
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <a href="#" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-blue-50 text-[#0039A6] font-semibold text-[10px] hover:bg-blue-100 transition">
                                    Ver perfil
                                </a>
                                <a href="#" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-[#FF6B00] text-white font-semibold text-[10px] hover:bg-orange-600 transition flex items-center justify-center gap-1">
                                    <i class="fa-brands fa-whatsapp text-xs"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Taller 2 -->
                        <div class="p-3 rounded-xl border border-gray-100 hover:border-blue-200 transition bg-gray-50/50">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#FF6B00] to-[#E55A00] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    ME
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h5 class="font-bold text-sm text-gray-900 truncate">Mecánica Express</h5>
                                        <span class="text-[10px] bg-green-100 text-green-800 px-1.5 py-0.5 rounded-full">✓</span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="font-bold text-gray-800 text-xs">4.6</span>
                                        <div class="text-[#FF6B00] flex text-[8px] gap-0.5">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                        </div>
                                        <span class="text-gray-400 text-[10px]">(96)</span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-gray-400"></i> 1.8 km
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <a href="#" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-blue-50 text-[#0039A6] font-semibold text-[10px] hover:bg-blue-100 transition">
                                    Ver perfil
                                </a>
                                <a href="#" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-[#FF6B00] text-white font-semibold text-[10px] hover:bg-orange-600 transition flex items-center justify-center gap-1">
                                    <i class="fa-brands fa-whatsapp text-xs"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="mt-3 inline-flex items-center gap-1 text-[#0039A6] font-semibold text-xs hover:underline">
                        Ver todos
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PREGUNTAS RÁPIDAS (Debajo del chat) -->
    <section class="max-w-7xl mx-auto px-4 pb-14 mt-6">
        <div class="bg-white rounded-2xl border border-blue-200/80 p-6 shadow-sm">
            <div class="flex items-center gap-2.5 mb-5">
                <i class="fa-solid fa-question-circle text-[#FF6B00] text-xl"></i>
                <h4 class="text-lg font-bold text-gray-900">Preguntas rápidas</h4>
                <span class="ml-auto text-xs text-gray-400">Haz clic para preguntar</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button type="button" class="flex items-center gap-2.5 p-3.5 rounded-xl border border-gray-200 hover:border-[#FF6B00] hover:bg-orange-50 transition group text-left">
                    <span class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-[#FF6B00]/20 text-gray-500 group-hover:text-[#FF6B00] flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-car text-sm"></i>
                    </span>
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">¿Por qué mi auto hace ese ruido?</span>
                </button>
                <button type="button" class="flex items-center gap-2.5 p-3.5 rounded-xl border border-gray-200 hover:border-[#FF6B00] hover:bg-orange-50 transition group text-left">
                    <span class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-[#FF6B00]/20 text-gray-500 group-hover:text-[#FF6B00] flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-calendar-check text-sm"></i>
                    </span>
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">¿Cada cuánto cambiar las pastillas?</span>
                </button>
                <button type="button" class="flex items-center gap-2.5 p-3.5 rounded-xl border border-gray-200 hover:border-[#FF6B00] hover:bg-orange-50 transition group text-left">
                    <span class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-[#FF6B00]/20 text-gray-500 group-hover:text-[#FF6B00] flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-book-open text-sm"></i>
                    </span>
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">¿Qué significa "delantero"?</span>
                </button>
                <button type="button" class="flex items-center gap-2.5 p-3.5 rounded-xl border border-gray-200 hover:border-[#FF6B00] hover:bg-orange-50 transition group text-left">
                    <span class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-[#FF6B00]/20 text-gray-500 group-hover:text-[#FF6B00] flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-gauge-high text-sm"></i>
                    </span>
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">¿Cómo mejorar el rendimiento?</span>
                </button>
            </div>
        </div>
    </section>
@endsection
