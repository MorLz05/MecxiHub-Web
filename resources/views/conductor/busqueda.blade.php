@extends('conductor.layouts.app')

@section('title', 'Buscar Talleres - MecxiHub')

@section('content')
    <!-- HERO SECTION (mantén tu bloque original tal cual) -->
    <section class="relative bg-[#002677] text-white pt-10 pb-32 px-4 sm:px-8 overflow-hidden">
        <!-- Background Shapes - Mismos que principal --> <svg
            class="absolute top-0 left-0 w-full h-full pointer-events-none z-0" viewBox="0 0 1440 480" fill="none"
            preserveAspectRatio="none">
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
        </svg> <!-- Imagen de Fondo - Misma que principal pero con imagen diferente -->
        <div class="absolute inset-y-0 right-0 w-full lg:w-3/5 pointer-events-none z-0"> <img
                src="https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1200&q=80"
                alt="Buscar taller"
                class="w-full h-full object-cover object-center opacity-75 sm:opacity-85 [mask-image:linear-gradient(to_right,transparent_0%,black_30%,black_70%,transparent_100%)]">
        </div> <!-- Onda Curva Naranja - Misma que principal --> <svg
            class="absolute top-0 right-0 h-full w-28 sm:w-48 lg:w-64 pointer-events-none z-0 text-[#FF6B00]"
            viewBox="0 0 200 500" fill="none" preserveAspectRatio="none">
            <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.85" />
            <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.4" />
        </svg> <!-- CONTENIDO DEL HERO - Misma estructura que principal -->
        <div class="max-w-7xl mx-auto relative z-10 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center"> <!-- Columna Izquierda: Información -->
                <div class="lg:col-span-8 space-y-8"> <!-- Header con Logo2 y Título Principal -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6"> <!-- Logo2 -->
                        <div class="flex-shrink-0 pr-6 sm:border-r sm:border-white/20"> <img
                                src="{{ asset('images/logo/logo2.png') }}" alt="MXH MecxiHub"
                                class="h-16 sm:h-20 w-auto object-contain"> </div>
                        <!-- Título Principal - MISMOS TAMAÑOS que principal -->
                        <div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight"> Encuentra
                                el <br> <span class="text-[#FF6B00]">taller ideal</span> </h2>
                            <p class="text-blue-100/90 text-sm sm:text-base mt-2 max-w-md font-normal"> Busca, compara y
                                elige el mejor taller para tu auto. </p>
                        </div>
                    </div> <!-- Lista de Características - Misma que principal -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div
                            class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-shield-halved text-[#FF6B00] text-lg"></i>
                            </div> <span class="text-xs sm:text-sm font-medium leading-tight">Talleres verificados<br>y
                                calificados</span>
                        </div>
                        <div
                            class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-robot text-[#FF6B00] text-lg"></i>
                            </div> <span class="text-xs sm:text-sm font-medium leading-tight">Diagnóstico inteligente<br>con
                                IA</span>
                        </div>
                        <div
                            class="flex items-center gap-3 bg-blue-950/40 sm:bg-transparent p-2.5 sm:p-0 rounded-xl border border-white/10 sm:border-none">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-star text-[#FF6B00] text-lg"></i>
                            </div> <span class="text-xs sm:text-sm font-medium leading-tight">Opiniones reales<br>de
                                clientes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN BUSCADOR -->
    <div class="max-w-7xl mx-auto px-4 -mt-12 relative z-20">
        <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border border-gray-100">
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                    <h3 class="text-2xl font-bold text-gray-900">Buscar talleres</h3>
                </div>
                <p class="text-gray-500 text-sm mt-1 ml-4">Encuentra talleres verificados y calificados por otros
                    clientes.</p>
            </div>

            <!-- Aviso de ubicación -->
            <div id="location-status" class="hidden mb-4"></div>

            <form action="{{ route('buscar.talleres') }}" method="GET" id="search-form" class="space-y-6">
                {{-- Hidden lat/lng --}}
                <input type="hidden" name="user_lat" id="user_lat" value="{{ $userLat ?? '' }}">
                <input type="hidden" name="user_lng" id="user_lng" value="{{ $userLng ?? '' }}">

                <!-- Search Input -->
                <div class="relative">
                    <div
                        class="flex items-center border border-gray-200 rounded-2xl overflow-hidden focus-within:border-[#0039A6] focus-within:ring-2 focus-within:ring-blue-100 bg-gray-50/50">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 pl-5 text-base"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                            placeholder="Taller López, frenos, suspensión..."
                            class="w-full pl-3 pr-4 py-4 border-0 focus:ring-0 bg-transparent text-gray-700 placeholder-gray-400 text-sm">
                        <button type="submit"
                            class="bg-[#FF6B00] text-white px-6 py-4 font-semibold hover:bg-orange-600 transition flex items-center gap-2">
                            <i class="fa-solid fa-search"></i>
                            Buscar
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    <!-- Distancia -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Distancia</label>
                        <select name="distancia"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100">
                            @foreach ([5 => '5 km', 10 => '10 km', 20 => '20 km', 50 => '50 km'] as $val => $lbl)
                                <option value="{{ $val }}" {{ ($distanciaMax ?? 10) == $val ? 'selected' : '' }}>
                                    {{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <!-- Especialidad (se omite funcionalidad) -->
                    <div class="hidden sm:block">
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Especialidad</label>
                        <select name="especialidad" disabled
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">
                            <option value="">Próximamente</option>
                        </select>
                    </div> --}}

                    <!-- Calificación -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Calificación</label>
                        <select name="calificacion"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100">
                            <option value="">Todas</option>
                            <option value="4.5" {{ ($calificacionMin ?? '') === '4.5' ? 'selected' : '' }}>4.5+ ★
                            </option>
                            <option value="4.0" {{ ($calificacionMin ?? '') === '4.0' ? 'selected' : '' }}>4.0+ ★
                            </option>
                            <option value="3.5" {{ ($calificacionMin ?? '') === '3.5' ? 'selected' : '' }}>3.5+ ★
                            </option>
                        </select>
                    </div>

                    {{-- <!-- Servicios (se omite funcionalidad) -->
                    <div class="hidden md:block">
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Servicios</label>
                        <select name="servicios" disabled
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">
                            <option value="">Próximamente</option>
                        </select>
                    </div>
 --}}
                    <!-- Ordenar por -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Ordenar
                            por</label>
                        <select name="orden"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100">
                            <option value="relevancia" {{ ($orden ?? '') === 'relevancia' ? 'selected' : '' }}>Relevancia
                            </option>
                            <option value="distancia" {{ ($orden ?? '') === 'distancia' ? 'selected' : '' }}>Distancia
                            </option>
                            <option value="calificacion" {{ ($orden ?? '') === 'calificacion' ? 'selected' : '' }}>
                                Calificación</option>
                            <option value="reviews" {{ ($orden ?? '') === 'reviews' ? 'selected' : '' }}>Más valorados
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Botón para activar ubicación --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" id="btn-ubicacion"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-[#0039A6] rounded-xl font-semibold text-xs transition border border-blue-200">
                        <i class="fa-solid fa-location-crosshairs"></i>
                        <span id="btn-ubicacion-text">Usar mi ubicación para filtrar por distancia</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RESULTADOS -->
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-list text-[#FF6B00] text-xl"></i>
                <h3 class="text-xl font-bold text-gray-900">
                    Resultados encontrados: <span class="text-[#FF6B00]">{{ $total ?? 0 }}</span> talleres
                </h3>
            </div>
            <div class="flex items-center gap-3">
                @if (!empty($talleres))
                    <button type="button" id="btn-ver-mapa"
                        class="text-[#0039A6] font-semibold text-sm hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-map-location-dot"></i> Ver en mapa
                    </button>
                @endif
            </div>
        </div>

        <!-- Lista de talleres -->
        @if (empty($talleres))
            <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fa-solid fa-store-slash text-5xl text-gray-300"></i>
                <h3 class="text-lg font-bold text-gray-800 mt-4">No encontramos talleres</h3>
                <p class="text-gray-500 text-sm mt-1">Prueba ampliando la distancia o cambiando el texto de búsqueda.</p>
                <a href="{{ route('buscar.talleres') }}"
                    class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-[#0039A6] hover:bg-blue-800 text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-rotate-right"></i> Limpiar filtros
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($talleres as $taller)
                    @php
                        $rating = (float) ($taller['rating_promedio'] ?? 0);
                        $reviews = (int) ($taller['total_resenas'] ?? 0);
                        $distancia = $taller['distancia_km'] ?? null;
                        $servicios = array_slice(
                            array_map(fn($s) => $s['nombre'] ?? '', $taller['servicios_count'] ?? []),
                            0,
                            3,
                        );
                        $tallerUrl = route('taller.perfil', ['id' => $taller['id']]);
                    @endphp
                    <div
                        class="bg-white rounded-2xl border border-blue-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex gap-4 items-start flex-1">
                            <!-- Image/Logo -->
                            @if (!empty($taller['imagen_principal']))
                                <img src="{{ $taller['imagen_principal'] }}" alt="{{ $taller['nombre'] }}"
                                    class="w-20 h-20 rounded-xl object-cover flex-shrink-0 border border-gray-100">
                            @else
                                <div
                                    class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center flex-shrink-0 text-white font-bold text-xl">
                                    {{ mb_strtoupper(mb_substr($taller['nombre'], 0, 2)) }}
                                </div>
                            @endif

                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-bold text-gray-900 text-base">{{ $taller['nombre'] }}</h4>
                                    @if ($taller['verificado'] ?? false)
                                        <i class="fa-solid fa-circle-check text-[#0039A6] text-sm"
                                            title="Taller verificado"></i>
                                    @endif
                                    @if (!empty($taller['direccion']))
                                        <span class="text-xs text-gray-400">•</span>
                                        <span
                                            class="text-xs text-gray-500 truncate max-w-xs">{{ $taller['direccion'] }}</span>
                                    @endif
                                </div>

                                <!-- Rating y Distancia -->
                                <div class="flex items-center gap-3 text-xs flex-wrap">
                                    @if ($reviews > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="font-bold text-gray-800">{{ number_format($rating, 1) }}</span>
                                            <div class="text-[#FF6B00] flex text-[10px] gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i
                                                        class="fa-solid fa-star {{ $i <= round($rating) ? '' : 'text-gray-300' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="text-gray-400">({{ $reviews }}
                                                {{ $reviews === 1 ? 'reseña' : 'reseñas' }})</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                            <i class="fa-regular fa-star"></i>
                                            Sin reseñas aún
                                        </span>
                                    @endif

                                    @if ($distancia !== null)
                                        <span class="text-gray-300">|</span>
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <i class="fa-solid fa-location-dot text-gray-400"></i>
                                            {{ number_format($distancia, 1) }} km de ti
                                        </span>
                                    @endif
                                </div>

                                <!-- Servicios chips -->
                                @if (!empty($servicios))
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach ($servicios as $servicio)
                                            <span
                                                class="text-xs bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full font-medium">
                                                {{ $servicio }}
                                            </span>
                                        @endforeach
                                        @if (count($taller['servicios_count'] ?? []) > 3)
                                            <span
                                                class="text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full font-medium">
                                                +{{ count($taller['servicios_count']) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                            <a href="{{ route('taller.perfil', ['id' => $taller['id']]) }}"
                                class="text-center py-2.5 px-4 rounded-xl bg-blue-50 text-[#0039A6] font-semibold text-xs hover:bg-blue-100 transition whitespace-nowrap">
                                Ver perfil
                            </a>
                            @if (!empty($taller['telefono']))
                                <a href="https://wa.me/52{{ preg_replace('/[^0-9]/', '', $taller['telefono']) }}"
                                    target="_blank" rel="noopener"
                                    class="text-center py-2.5 px-4 rounded-xl bg-[#FF6B00] text-white font-semibold text-xs hover:bg-orange-600 transition flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            @if (($totalPages ?? 1) > 1)
                <div class="flex justify-center mt-10">
                    <nav class="flex items-center gap-1">
                        {{-- Anterior --}}
                        @if ($page > 1)
                            <a href="{{ route('buscar.talleres', array_merge(request()->query(), ['page' => $page - 1])) }}"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition text-sm">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        @endif

                        @php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                        @endphp

                        @if ($start > 1)
                            <a href="{{ route('buscar.talleres', array_merge(request()->query(), ['page' => 1])) }}"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition text-sm">1</a>
                            @if ($start > 2)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <a href="{{ route('buscar.talleres', array_merge(request()->query(), ['page' => $i])) }}"
                                class="px-4 py-2 rounded-xl text-sm {{ $i == $page ? 'bg-[#0039A6] text-white font-semibold' : 'border border-gray-200 text-gray-600 hover:bg-gray-50 transition' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if ($end < $totalPages)
                            @if ($end < $totalPages - 1)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                            <a href="{{ route('buscar.talleres', array_merge(request()->query(), ['page' => $totalPages])) }}"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition text-sm">{{ $totalPages }}</a>
                        @endif

                        {{-- Siguiente --}}
                        @if ($page < $totalPages)
                            <a href="{{ route('buscar.talleres', array_merge(request()->query(), ['page' => $page + 1])) }}"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition text-sm">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>

    {{-- ============================================================ --}}
    {{-- MODAL DE MAPA CON PINES DE TALLERES --}}
    {{-- ============================================================ --}}
    <div id="modal-mapa-talleres" class="fixed inset-0 z-[9999] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarModalMapa()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[88vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center">
                            <i class="fa-solid fa-map-location-dot text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Talleres en el mapa</h3>
                            <p class="text-xs text-gray-500">Haz clic en un pin para ver los detalles del taller.</p>
                        </div>
                    </div>
                    <button type="button" onclick="cerrarModalMapa()"
                        class="w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-500 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="flex-1 min-h-0 relative">
                    <div id="mapa-talleres" class="w-full h-full bg-gray-100"></div>
                    <div id="mapa-loading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 border-3 border-[#0039A6] border-t-transparent rounded-full animate-spin">
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Cargando mapa...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Google Maps API --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=marker&language=es&region=MX&loading=async&callback=initBusquedaMaps"
        async defer></script>

    @php
        $talleresParaMapa = array_map(function ($t) {
            return [
                'id' => $t['id'] ?? '',
                'nombre' => $t['nombre'] ?? '',
                'direccion' => $t['direccion'] ?? '',
                'latitud' => $t['latitud'] ?? null,
                'longitud' => $t['longitud'] ?? null,
                'rating' => $t['calificacion_promedio'] ?? 0,
                'reviews' => $t['total_resenas'] ?? 0,
                'telefono' => $t['telefono'] ?? '',
                'url' => route('taller.perfil', ['id' => $t['id'] ?? '']),
            ];
        }, $talleres ?? []);
    @endphp

    <script>
        window.TALLERES_MAPA = @json($talleresParaMapa);

        let mapaTalleres = null;
        let marcadores = [];

        function initBusquedaMaps() {
            console.log('✅ Google Maps API lista para búsqueda');
            window.GOOGLE_MAPS_READY = true;
        }

        window.cerrarModalMapa = function() {
            document.getElementById('modal-mapa-talleres').classList.add('hidden');
            document.body.style.overflow = '';
        };

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modal-mapa-talleres');
            const btnVerMapa = document.getElementById('btn-ver-mapa');
            const btnUbicacion = document.getElementById('btn-ubicacion');
            const locationStatus = document.getElementById('location-status');
            const inputLat = document.getElementById('user_lat');
            const inputLng = document.getElementById('user_lng');
            const formBusqueda = document.getElementById('search-form');

            // ============ BOTÓN VER EN MAPA ============
            btnVerMapa?.addEventListener('click', function() {
                if (!window.GOOGLE_MAPS_READY) {
                    alert('Google Maps aún se está cargando. Espera un momento.');
                    return;
                }
                abrirModalMapa();
            });

            function abrirModalMapa() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                setTimeout(() => {
                    inicializarMapa();
                }, 100);
            }

            function inicializarMapa() {
                const talleres = window.TALLERES_MAPA || [];
                const contenedor = document.getElementById('mapa-talleres');

                if (!contenedor) return;

                // Centro por defecto: CDMX
                const defaultCenter = {
                    lat: 19.4326,
                    lng: -99.1332
                };

                // Centro: promedio de talleres con coords, o CDMX
                const conCoords = talleres.filter(t => t.latitud && t.longitud);
                let centro = defaultCenter;
                if (conCoords.length > 0) {
                    const sumLat = conCoords.reduce((a, t) => a + parseFloat(t.latitud), 0);
                    const sumLng = conCoords.reduce((a, t) => a + parseFloat(t.longitud), 0);
                    centro = {
                        lat: sumLat / conCoords.length,
                        lng: sumLng / conCoords.length
                    };
                }

                // Destruir mapa previo
                if (mapaTalleres) {
                    mapaTalleres = null;
                    marcadores = [];
                }

                mapaTalleres = new google.maps.Map(contenedor, {
                    center: centro,
                    zoom: 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                    mapId: 'DEMO_MAP_ID',
                });

                // Info Window compartida
                const infoWindow = new google.maps.InfoWindow();

                // Añadir marcadores
                conCoords.forEach(t => {
                    const pos = {
                        lat: parseFloat(t.latitud),
                        lng: parseFloat(t.longitud)
                    };

                    const marker = new google.maps.marker.AdvancedMarkerElement({
                        map: mapaTalleres,
                        position: pos,
                        title: t.nombre,
                    });

                    // Contenido del popup
                    const content = `
                        <div style="min-width: 220px; font-family: sans-serif;">
                            <div style="font-weight: 700; color: #001B5E; font-size: 14px; margin-bottom: 4px;">
                                ${t.nombre}
                            </div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">
                                ${t.direccion}
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #FF6B00; font-weight: 600; margin-bottom: 10px;">
                                <span>★ ${parseFloat(t.rating).toFixed(1)}</span>
                                <span style="color: #9ca3af; font-weight: 400;">(${t.reviews} reseñas)</span>
                            </div>
                            <a href="${t.url}" style="display: inline-block; padding: 8px 14px; background: #FF6B00; color: white; text-decoration: none; border-radius: 8px; font-size: 12px; font-weight: 600;">
                                Ver perfil →
                            </a>
                        </div>
                    `;

                    marker.addListener('click', () => {
                        infoWindow.setContent(content);
                        infoWindow.open(mapaTalleres, marker);
                    });

                    marcadores.push(marker);
                });

                // Ocultar loading
                const loading = document.getElementById('mapa-loading');
                if (loading) loading.style.display = 'none';

                // Si el usuario tiene ubicación, mostrar un pin distinto
                const userLat = parseFloat(inputLat.value);
                const userLng = parseFloat(inputLng.value);
                if (!isNaN(userLat) && !isNaN(userLng)) {
                    const userPin = document.createElement('div');
                    userPin.innerHTML =
                        '<div style="width: 20px; height: 20px; background: #0066FF; border: 3px solid white; border-radius: 50%; box-shadow: 0 0 0 4px rgba(0,102,255,0.3);"></div>';

                    new google.maps.marker.AdvancedMarkerElement({
                        map: mapaTalleres,
                        position: {
                            lat: userLat,
                            lng: userLng
                        },
                        title: 'Tu ubicación',
                        content: userPin,
                    });
                }
            }

            // ============ BOTÓN UBICACIÓN ============
            btnUbicacion?.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    mostrarAviso('Tu navegador no soporta geolocalización.', 'error');
                    return;
                }

                this.disabled = true;
                this.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> <span>Obteniendo ubicación...</span>';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;

                        inputLat.value = lat;
                        inputLng.value = lng;

                        mostrarAviso('✅ Ubicación detectada. Aplicando filtros...', 'success');

                        // Enviar el form automáticamente
                        setTimeout(() => formBusqueda.submit(), 500);
                    },
                    (err) => {
                        this.disabled = false;
                        this.innerHTML =
                            '<i class="fa-solid fa-location-crosshairs"></i> <span>Usar mi ubicación para filtrar por distancia</span>';

                        let msg = 'No se pudo obtener tu ubicación.';
                        if (err.code === err.PERMISSION_DENIED) {
                            msg =
                                'Permiso de ubicación denegado. Actívalo en la configuración del navegador (icono del candado junto a la URL).';
                        } else if (err.code === err.POSITION_UNAVAILABLE) {
                            msg = 'Tu ubicación no está disponible.';
                        } else if (err.code === err.TIMEOUT) {
                            msg = 'La solicitud de ubicación tardó demasiado.';
                        }

                        mostrarAviso(msg, 'error');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 8000
                    }
                );
            });

            // ============ AVISO ============
            function mostrarAviso(msg, tipo = 'info') {
                if (!locationStatus) return;

                const estilos = {
                    info: 'bg-blue-50 border-blue-200 text-blue-700',
                    success: 'bg-emerald-50 border-emerald-200 text-emerald-700',
                    error: 'bg-red-50 border-red-200 text-red-700',
                };
                const iconos = {
                    info: 'fa-circle-info',
                    success: 'fa-circle-check',
                    error: 'fa-circle-exclamation',
                };

                locationStatus.className =
                    `mb-4 p-3 rounded-xl border ${estilos[tipo]} flex items-center gap-2 text-sm`;
                locationStatus.innerHTML =
                    `<i class="fa-solid ${iconos[tipo]}"></i> <span>${msg}</span>`;
                locationStatus.classList.remove('hidden');
            }

            // Mostrar aviso si ya tenemos la ubicación (viene del backend)
            if (inputLat.value && inputLng.value) {
                mostrarAviso('📍 Ubicación aplicada. Los talleres están ordenados/filtrados por distancia.',
                    'success');
            }

            // ============ AUTO-SUBMIT de filtros al cambiar ============
            formBusqueda.querySelectorAll('select').forEach(sel => {
                sel.addEventListener('change', () => formBusqueda.submit());
            });

            // ESC cierra el modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    cerrarModalMapa();
                }
            });
        });
    </script>
@endsection
