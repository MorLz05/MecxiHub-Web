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

        {{-- Aviso de ubicación --}}
        <div id="location-banner" class="hidden mb-6 p-4 rounded-2xl border flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" id="location-banner-icon-container">
                    <i class="fa-solid fa-location-crosshairs text-lg" id="location-banner-icon"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm" id="location-banner-title">Activa tu ubicación</p>
                    <p class="text-xs opacity-80" id="location-banner-text">Encuentra los talleres más cercanos a ti.</p>
                </div>
            </div>
            <button type="button" id="btn-activar-ubicacion"
                class="shrink-0 px-4 py-2.5 rounded-xl bg-[#0039A6] hover:bg-blue-800 text-white font-semibold text-xs transition whitespace-nowrap">
                <i class="fa-solid fa-location-crosshairs mr-1"></i> Usar mi ubicación
            </button>
        </div>

        <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-star text-[#FF6B00] text-xl"></i>
                <h3 class="text-xl font-bold text-gray-900">
                    @if ($tieneUbicacion)
                        Talleres cerca de ti
                    @else
                        Talleres mejor valorados
                    @endif
                </h3>
            </div>
            <a href="{{ route('buscar.talleres') }}"
                class="text-[#0039A6] font-semibold text-sm hover:underline flex items-center gap-1">
                Ver todos <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
        </div>

        @if (empty($talleres))
            {{-- Estado vacío --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fa-solid fa-store-slash text-5xl text-gray-300"></i>
                <h3 class="text-lg font-bold text-gray-800 mt-4">Aún no hay talleres disponibles</h3>
                <p class="text-gray-500 text-sm mt-1">Vuelve más tarde para descubrir talleres verificados.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($talleres as $taller)
                    @php
                        $rating = (float) ($taller['calificacion_promedio'] ?? 0);
                        $reviews = (int) ($taller['total_resenas'] ?? 0);
                        $distancia = $taller['distancia_km'] ?? null;
                        $logoUrl = $taller['imagen_principal'] ?? null;
                        $iniciales = '';
                        foreach (preg_split('/\s+/', trim($taller['nombre'] ?? 'T')) as $p) {
                            if (!empty($p)) $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
                            if (mb_strlen($iniciales) >= 2) break;
                        }
                        if (empty($iniciales)) $iniciales = 'T';
                    @endphp

                    <div class="bg-white rounded-2xl border border-blue-200/80 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex gap-4 items-start">
                                <!-- Image / Logo -->
                                @if ($logoUrl)
                                    <div class="w-24 h-24 rounded-xl bg-white border border-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img src="{{ $logoUrl }}" alt="{{ $taller['nombre'] }}"
                                            class="w-full h-full object-contain p-2">
                                    </div>
                                @else
                                    <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center text-white font-black text-2xl shrink-0">
                                        {{ $iniciales }}
                                    </div>
                                @endif

                                <!-- Content -->
                                <div class="space-y-1 flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-gray-900 text-base truncate">{{ $taller['nombre'] }}</h4>
                                        @if ($taller['verificado'] ?? false)
                                            <i class="fa-solid fa-circle-check text-[#0039A6] text-sm shrink-0" title="Taller verificado"></i>
                                        @endif
                                    </div>

                                    @if (!empty($taller['especialidades_str']))
                                        <p class="text-xs text-gray-500 truncate">Especialidad: {{ $taller['especialidades_str'] }}</p>
                                    @elseif (!empty($taller['direccion']))
                                        <p class="text-xs text-gray-500 truncate">{{ $taller['direccion'] }}</p>
                                    @endif

                                    <!-- Rating -->
                                    <div class="flex items-center gap-1 text-xs pt-1">
                                        <span class="font-bold text-gray-800">{{ number_format($rating, 1) }}</span>
                                        <div class="text-[#FF6B00] flex text-[10px] gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa-solid fa-star {{ $i <= round($rating) ? '' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-gray-400">({{ $reviews }})</span>
                                    </div>

                                    <!-- Distance -->
                                    <p class="text-xs text-gray-500 flex items-center gap-1 pt-1">
                                        @if ($distancia !== null)
                                            <i class="fa-solid fa-location-dot text-gray-400"></i>
                                            A {{ $distancia }} km de ti
                                        @elseif (!empty($taller['direccion']))
                                            <i class="fa-solid fa-location-dot text-gray-400"></i>
                                            <span class="truncate">{{ $taller['direccion'] }}</span>
                                        @else
                                            <i class="fa-solid fa-location-dot text-gray-400"></i>
                                            Ubicación no disponible
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2 mt-5">
                            <a href="{{ route('taller.perfil', ['id' => $taller['id']]) }}"
                                class="text-center py-2.5 px-3 rounded-xl bg-blue-50 text-[#0039A6] font-semibold text-xs hover:bg-blue-100 transition">
                                Ver perfil
                            </a>
                            @php
                                $tel = preg_replace('/[^0-9]/', '', $taller['telefono'] ?? '');
                            @endphp
                            @if ($tel)
                                <a href="https://wa.me/52{{ $tel }}" target="_blank" rel="noopener"
                                    class="text-center py-2.5 px-3 rounded-xl bg-[#FF6B00] text-white font-semibold text-xs hover:bg-orange-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                                </a>
                            @else
                                <a href="{{ route('taller.perfil', ['id' => $taller['id']]) }}"
                                    class="text-center py-2.5 px-3 rounded-xl bg-[#FF6B00] text-white font-semibold text-xs hover:bg-orange-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-arrow-right text-sm"></i> Visitar
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- JS para detección de ubicación --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const banner = document.getElementById('location-banner');
                const btnActivar = document.getElementById('btn-activar-ubicacion');
                const tieneUbicacion = @json($tieneUbicacion ?? false);

                // Mostrar el banner si no tiene ubicación
                if (!tieneUbicacion && banner) {
                    banner.classList.remove('hidden');
                    banner.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-800');
                    document.getElementById('location-banner-icon-container').classList.add('bg-blue-100');
                    document.getElementById('location-banner-icon').classList.add('text-blue-600');
                }

                btnActivar?.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        alert('Tu navegador no soporta geolocalización.');
                        return;
                    }

                    this.disabled = true;
                    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Obteniendo ubicación...';

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;

                            // Redirigir con query params
                            const url = new URL(window.location.href);
                            url.searchParams.set('user_lat', lat.toFixed(6));
                            url.searchParams.set('user_lng', lng.toFixed(6));
                            window.location.href = url.toString();
                        },
                        (err) => {
                            this.disabled = false;
                            this.innerHTML = '<i class="fa-solid fa-location-crosshairs mr-1"></i> Usar mi ubicación';

                            let msg = 'No se pudo obtener tu ubicación.';
                            if (err.code === err.PERMISSION_DENIED) {
                                msg = 'Permiso de ubicación denegado. Actívalo en tu navegador (icono del candado junto a la URL).';
                            } else if (err.code === err.TIMEOUT) {
                                msg = 'La solicitud tardó demasiado. Intenta de nuevo.';
                            } else if (err.code === err.POSITION_UNAVAILABLE) {
                                msg = 'Ubicación no disponible.';
                            }
                            alert(msg);
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                });
            });
        </script>
    @endpush
@endsection
