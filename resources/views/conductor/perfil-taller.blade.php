@extends('conductor.layouts.app')

@php
    // Preparamos algunos valores útiles
    $logoUrl = $taller['logo_url'] ?? null;
    $telefonoLimpio = preg_replace('/[^0-9]/', '', $taller['telefono'] ?? '');
    $waLink = $telefonoLimpio ? "https://wa.me/52{$telefonoLimpio}" : '#';
    $hayResenas = $totalResenas > 0;
    $hayImagenes = count($imagenes) > 0;
    $hayUbicacion = !empty($taller['latitud']) && !empty($taller['longitud']);
@endphp

@section('title', ($taller['nombre'] ?? 'Taller') . ' - MecxiHub')

@section('content')
    <!-- HERO -->
    <section class="relative bg-[#002677] text-white pt-10 pb-28 px-4 sm:px-8 overflow-hidden">
        <svg class="absolute top-0 left-0 w-full h-full pointer-events-none z-0" viewBox="0 0 1440 480" fill="none"
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
        </svg>

        <svg class="absolute top-0 right-0 h-full w-28 sm:w-48 lg:w-64 pointer-events-none z-0 text-[#FF6B00]"
            viewBox="0 0 200 500" fill="none" preserveAspectRatio="none">
            <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.85" />
            <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.4" />
        </svg>

        <div class="max-w-7xl mx-auto relative z-10 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-6">
                    <nav class="flex items-center gap-2 text-sm text-blue-200/80 flex-wrap">
                        <a href="{{ route('home') }}" class="hover:text-white transition">Inicio</a>
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                        <a href="{{ route('buscar.talleres') }}" class="hover:text-white transition">Buscar talleres</a>
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                        <span class="text-white font-medium">{{ $taller['nombre'] }}</span>
                    </nav>

                    <div class="flex flex-col sm:flex-row items-start gap-6">
                        <div class="flex-shrink-0">
                            <div
                                class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-2 shadow-xl border-2 border-white/30 flex items-center justify-center overflow-hidden">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $taller['nombre'] }}"
                                        class="w-full h-full object-contain">
                                @else
                                    <div
                                        class="w-full h-full rounded-xl bg-gradient-to-br from-[#0039A6] to-[#001B5E] flex items-center justify-center text-white font-black text-3xl">
                                        {{ mb_strtoupper(mb_substr($taller['nombre'], 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                                    {{ $taller['nombre'] }}
                                </h1>
                                @if ($taller['verificado'])
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-[#0039A6] text-white text-xs font-semibold px-3 py-1.5 rounded-full border border-white/20">
                                        <i class="fa-solid fa-circle-check text-[#FF6B00]"></i>
                                        Verificado
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-4 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <span class="text-2xl font-bold text-white">{{ number_format($promedio, 1) }}</span>
                                        <div class="text-[#FF6B00] flex text-sm gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fa-solid fa-star {{ $i <= round($promedio) ? '' : 'text-white/20' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-blue-200 text-sm">
                                        ({{ $totalResenas }} {{ $totalResenas === 1 ? 'reseña' : 'reseñas' }})
                                    </span>
                                </div>

                                @if (!empty($taller['direccion']))
                                    <span class="text-blue-300/50">|</span>
                                    <span class="flex items-center gap-2 text-blue-100 text-sm">
                                        <i class="fa-solid fa-location-dot text-[#FF6B00]"></i>
                                        {{ $taller['direccion'] }}
                                    </span>
                                @endif
                            </div>

                            @if (!empty($especialidades))
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @foreach ($especialidades as $esp)
                                        <span
                                            class="text-xs bg-white/15 backdrop-blur-sm text-white px-3 py-1.5 rounded-full font-medium border border-white/20">
                                            {{ $esp }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 flex lg:justify-end">
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto">
                        @if ($telefonoLimpio)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                class="flex items-center justify-center gap-2 bg-[#FF6B00] text-white px-6 py-3.5 rounded-xl font-semibold hover:bg-orange-600 transition shadow-lg shadow-orange-500/25">
                                <i class="fa-brands fa-whatsapp text-lg"></i>
                                Contactar por WhatsApp
                            </a>
                        @endif
                        <button onclick="document.getElementById('seccion-ubicacion')?.scrollIntoView({behavior:'smooth'})"
                            class="flex items-center justify-center gap-2 bg-white/15 backdrop-blur-sm text-white px-6 py-3.5 rounded-xl font-semibold hover:bg-white/25 transition border border-white/20">
                            <i class="fa-regular fa-calendar-check"></i>
                            Ver ubicación y horarios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="max-w-7xl mx-auto px-4 -mt-16 relative z-20 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                {{-- DESCRIPCIÓN --}}
                @if (!empty($taller['descripcion']))
                    <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                            <h2 class="text-2xl font-bold text-gray-900">Sobre el taller</h2>
                        </div>
                        <div class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $taller['descripcion'] }}</div>
                    </div>
                @endif

                {{-- SERVICIOS --}}
                @if (!empty($servicios))
                    <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                                <h2 class="text-2xl font-bold text-gray-900">Servicios</h2>
                            </div>
                            <span class="text-sm text-gray-400">{{ count($servicios) }} servicios disponibles</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($servicios as $servicio)
                                <div
                                    class="flex items-start gap-4 p-4 rounded-2xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/30 transition group">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-[#0039A6] transition">
                                        <i
                                            class="{{ $servicio['icono'] }} text-[#0039A6] text-lg group-hover:text-white transition"></i>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3 flex-wrap">
                                            <h3 class="font-bold text-gray-900 text-base">{{ $servicio['nombre'] }}</h3>
                                            @if (!empty($servicio['precio']))
                                                <p class="text-xs font-semibold text-gray-700">
                                                    <i class="fa-solid fa-dollar-sign text-[#FF8800]"></i>
                                                    {{ $servicio['precio'] }}
                                                </p>
                                            @endif
                                        </div>
                                        @if (!empty($servicio['descripcion']))
                                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">
                                                {{ $servicio['descripcion'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- GALERÍA --}}
                @if ($hayImagenes)
                    <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                            <h2 class="text-2xl font-bold text-gray-900">Galería del taller</h2>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($imagenes as $index => $img)
                                <div
                                    class="relative group cursor-pointer overflow-hidden rounded-2xl {{ $index === 0 ? 'col-span-2 row-span-2' : '' }}">
                                    <img src="{{ $img['url'] }}"
                                        alt="{{ $img['nombre_original'] ?? 'Imagen del taller' }}"
                                        class="w-full h-full object-cover aspect-square group-hover:scale-105 transition duration-500">
                                    <a href="{{ $img['url'] }}" target="_blank" rel="noopener"
                                        class="absolute inset-0 bg-black/0 hover:bg-black/40 transition flex items-center justify-center">
                                        <i
                                            class="fa-solid fa-expand text-white text-2xl opacity-0 group-hover:opacity-100 transition"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- UBICACIÓN --}}
                <div id="seccion-ubicacion" class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                        <h2 class="text-2xl font-bold text-gray-900">Ubicación</h2>
                    </div>

                    @if ($hayUbicacion)
                        <div class="relative rounded-2xl overflow-hidden border border-gray-200 h-64 sm:h-80">
                            <div id="mapa-perfil" class="w-full h-full bg-gray-100"></div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-10 text-center">
                            <i class="fa-solid fa-map-location-dot text-4xl text-gray-300"></i>
                            <p class="text-sm text-gray-500 mt-3">Este taller aún no ha registrado su ubicación exacta.</p>
                        </div>
                    @endif

                    @if (!empty($taller['direccion']))
                        <div class="mt-6 flex items-start gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-location-dot text-[#0039A6]"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $taller['nombre'] }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $taller['direccion'] }}</p>
                                @if (!is_null($distancia))
                                    <p class="text-[#0039A6] text-xs font-medium mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-route"></i>
                                        {{ $distancia }} km de ti
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (!empty($taller['horario']))
                        @php
                            // Agrupar días con el mismo horario (igual que en informacion)
                            $diasOrden = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                            $grupos = [];
                            foreach ($diasOrden as $dia) {
                                if (empty($taller['horario'][$dia])) {
                                    continue;
                                }
                                $key = json_encode($taller['horario'][$dia]);
                                if (!isset($grupos[$key])) {
                                    $grupos[$key] = ['dias' => [], 'rangos' => $taller['horario'][$dia]];
                                }
                                $grupos[$key]['dias'][] = $dia;
                            }
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                            @foreach ($grupos as $grupo)
                                @php
                                    $primerDia = ucfirst($grupo['dias'][0]);
                                    $ultimoDia = ucfirst(end($grupo['dias']));
                                    $rangoDias =
                                        count($grupo['dias']) === 1 ? $primerDia : $primerDia . ' a ' . $ultimoDia;
                                    $rangoHoras = collect($grupo['rangos'])
                                        ->map(fn($r) => ($r['apertura'] ?? '') . ' - ' . ($r['cierre'] ?? ''))
                                        ->implode(' / ');
                                @endphp
                                <div class="flex items-center gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                    <div
                                        class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-regular fa-clock text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ $rangoDias }}</p>
                                        <p class="font-semibold text-gray-900 text-sm">{{ $rangoHoras }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- RESEÑAS --}}
                <div id="seccion-resenas" class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-1.5 h-7 bg-[#FF6B00] rounded-full"></span>
                        <h2 class="text-2xl font-bold text-gray-900">Reseñas y comentarios</h2>
                    </div>

                    {{-- Flash messages --}}
                    @if (session('success'))
                        <div
                            class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div
                            class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('info'))
                        <div
                            class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            {{ session('info') }}
                        </div>
                    @endif

                    @if ($hayResenas)
                        {{-- Resumen de calificaciones --}}
                        <div
                            class="flex flex-col sm:flex-row items-center gap-8 p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-50/50 border border-blue-100 mb-8">

                            {{-- Promedio grande a la izquierda --}}
                            <div class="text-center flex-shrink-0">
                                <p class="text-5xl font-black text-[#0039A6] leading-none">
                                    {{ number_format($promedio, 1) }}
                                </p>
                                <div class="text-[#FF6B00] flex text-lg gap-1 mt-3 justify-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fa-solid fa-star {{ $i <= round($promedio) ? '' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-gray-500 text-sm mt-2">
                                    {{ $totalResenas }}
                                    {{ $totalResenas === 1 ? 'reseña' : 'reseñas' }}
                                </p>
                            </div>

                            {{-- Barras de distribución a la derecha --}}
                            <div class="flex-1 w-full space-y-2">
                                @foreach ([5, 4, 3, 2, 1] as $est)
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 w-6 font-medium">{{ $est }}★</span>
                                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#FF6B00] rounded-full transition-all duration-500"
                                                style="width: {{ $porcentajes[$est] ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-10 text-right">
                                            {{ $porcentajes[$est] ?? 0 }}%
                                        </span>
                                        <span class="text-xs text-gray-400 w-10 text-right hidden sm:inline">
                                            ({{ $distribucion[$est] ?? 0 }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endif

                    {{-- ============ FORMULARIO / CTA ============ --}}
                    @if (!$estaLogueado)
                        {{-- CTA login --}}
                        <div
                            class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-[#0039A6] to-[#0066FF] text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-regular fa-user-circle text-2xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-base">¿Ya eres cliente de este taller?</p>
                                    <p class="text-xs text-blue-100">Inicia sesión o crea tu cuenta para dejar tu reseña
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <a href="{{ route('login') }}"
                                    class="flex-1 sm:flex-none text-center px-5 py-2.5 rounded-xl bg-white text-[#0039A6] font-semibold text-sm hover:bg-blue-50 transition whitespace-nowrap">
                                    Iniciar sesión
                                </a>
                                <a href="{{ route('register') }}"
                                    class="flex-1 sm:flex-none text-center px-5 py-2.5 rounded-xl bg-[#FF6B00] text-white font-semibold text-sm hover:bg-orange-600 transition whitespace-nowrap">
                                    Registrarme
                                </a>
                            </div>
                        </div>
                    @elseif ($yaReseno)
                        <div
                            class="mb-8 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            Ya has dejado una reseña para este servicio. ¡Gracias!
                        </div>
                    @else
                        {{-- Formulario --}}
                        <form action="{{ route('taller.resena.store', ['id' => $taller['id']]) }}" method="POST"
                            x-data="{ rating: 0, hover: 0 }" class="mb-8 p-6 rounded-2xl bg-gray-50 border border-gray-100">
                            @csrf
                            <input type="hidden" name="token" value="{{ $tokenCalificacion }}">

                            <h3 class="font-bold text-gray-900 text-lg mb-4">Deja tu reseña</h3>

                            {{-- Estrellas --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">
                                    Tu calificación
                                </label>
                                <div class="flex items-center gap-1">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button" @click="rating = i" @mouseenter="hover = i"
                                            @mouseleave="hover = 0" class="text-3xl transition"
                                            :class="(hover || rating) >= i ? 'text-[#FF6B00]' : 'text-gray-300'">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </template>
                                    <span class="ml-3 text-sm text-gray-500 font-medium"
                                        x-text="rating ? rating + ' de 5' : ''"></span>
                                </div>
                                <input type="hidden" name="rating" x-model="rating">
                                @error('rating')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Comentario --}}
                            <div class="mb-4">
                                <label for="comentario"
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">
                                    Tu comentario
                                </label>
                                <textarea name="comentario" id="comentario" rows="4" maxlength="1000"
                                    placeholder="Cuéntanos tu experiencia con este taller..."
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 resize-none">{{ old('comentario') }}</textarea>
                                @error('comentario')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="rating === 0"
                                    class="px-6 py-3 rounded-xl bg-[#FF6B00] text-white font-semibold hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <i class="fa-regular fa-paper-plane"></i>
                                    Publicar reseña
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- ============ LISTA DE RESEÑAS ============ --}}
                    @if ($hayResenas)
                        @php
                            $resenasVisibles = array_slice($resenas, 0, 5);
                            $resenasOcultas = array_slice($resenas, 5);
                            $hayMasResenas = count($resenasOcultas) > 0;
                        @endphp

                        <div class="space-y-6" id="lista-resenas">
                            {{-- Primeras 5 reseñas (siempre visibles) --}}
                            @foreach ($resenasVisibles as $resena)
                                @include('conductor.partials.resena-item', ['resena' => $resena])
                            @endforeach

                            {{-- Resto de reseñas (ocultas al inicio) --}}
                            @if ($hayMasResenas)
                                <div id="resenas-ocultas" class="space-y-6 hidden">
                                    @foreach ($resenasOcultas as $resena)
                                        @include('conductor.partials.resena-item', ['resena' => $resena])
                                    @endforeach
                                </div>

                                {{-- Botón Ver más --}}
                                <div class="text-center pt-2">
                                    <button type="button" id="btn-ver-mas-resenas"
                                        data-total="{{ count($resenasOcultas) }}" onclick="toggleResenas()"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border-2 border-[#0039A6]/20 bg-white text-[#0039A6] font-semibold text-sm hover:bg-blue-50 hover:border-[#0039A6]/40 transition shadow-sm">
                                        <i class="fa-solid fa-chevron-down transition-transform"
                                            id="btn-ver-mas-icon"></i>
                                        <span id="btn-ver-mas-texto">
                                            Ver {{ count($resenasOcultas) }}
                                            {{ count($resenasOcultas) === 1 ? 'reseña más' : 'reseñas más' }}
                                        </span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-star text-4xl text-gray-300"></i>
                            <p class="text-sm text-gray-500 mt-3 font-medium">Este taller aún no tiene reseñas</p>
                            <p class="text-xs text-gray-400">Sé el primero en calificar un servicio</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- SIDEBAR -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100 sticky top-28">
                    <h3 class="font-bold text-gray-900 text-lg mb-4">¿Te interesa este taller?</h3>

                    <div class="space-y-3">
                        @if ($telefonoLimpio)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                class="w-full flex items-center justify-center gap-2 bg-[#FF6B00] text-white px-6 py-3.5 rounded-xl font-semibold hover:bg-orange-600 transition shadow-lg shadow-orange-500/20">
                                <i class="fa-brands fa-whatsapp text-lg"></i>
                                Enviar WhatsApp
                            </a>
                        @endif

                        @if ($hayUbicacion)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $taller['latitud'] }},{{ $taller['longitud'] }}"
                                target="_blank" rel="noopener"
                                class="w-full flex items-center justify-center gap-2 bg-[#0039A6] text-white px-6 py-3.5 rounded-xl font-semibold hover:bg-blue-800 transition">
                                <i class="fa-solid fa-route"></i>
                                Cómo llegar
                            </a>
                        @endif
                    </div>

                    <hr class="my-6 border-gray-100">

                    <div class="space-y-4">
                        @if ($taller['telefono'])
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-phone text-[#0039A6]"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Teléfono</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $taller['telefono'] }}</p>
                                </div>
                            </div>
                        @endif

                        {{--  @if ($taller['email'])
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-regular fa-envelope text-[#0039A6]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $taller['email'] }}</p>
                                </div>
                            </div>
                        @endif --}}

                        @if (!empty($taller['direccion']))
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-location-dot text-[#0039A6]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">Dirección</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $taller['direccion'] }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Google Maps --}}
    @if ($hayUbicacion)
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=marker&language=es&region=MX&loading=async&callback=initPerfilMaps"
            async defer></script>

        <script>
            function initPerfilMaps() {
                window.PERFIL_MAPS_READY = true;
                if (document.getElementById('mapa-perfil')) {
                    renderMapaPerfil();
                }
            }

            function renderMapaPerfil() {
                const contenedor = document.getElementById('mapa-perfil');
                if (!contenedor || !window.PERFIL_MAPS_READY) return;

                const pos = {
                    lat: {{ (float) $taller['latitud'] }},
                    lng: {{ (float) $taller['longitud'] }}
                };

                const map = new google.maps.Map(contenedor, {
                    center: pos,
                    zoom: 16,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                    mapId: 'DEMO_MAP_ID',
                });

                new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: pos,
                    title: @json($taller['nombre']),
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (window.PERFIL_MAPS_READY) renderMapaPerfil();
            });
        </script>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if ($abrirFormResena)
                    const seccion = document.getElementById('seccion-resenas');
                    if (seccion) {
                        setTimeout(() => seccion.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        }), 300);
                    }
                @endif
            });

            // Toggle "Ver más reseñas"
            function toggleResenas() {
                const contenedor = document.getElementById('resenas-ocultas');
                const boton = document.getElementById('btn-ver-mas-resenas');
                const icono = document.getElementById('btn-ver-mas-icon');
                const texto = document.getElementById('btn-ver-mas-texto');

                if (!contenedor || !boton) return;

                const totalOcultas = boton.dataset.total || '0';
                const estaOculto = contenedor.classList.contains('hidden');

                if (estaOculto) {
                    // Mostrar el resto
                    contenedor.classList.remove('hidden');
                    contenedor.style.opacity = '0';
                    contenedor.style.transition = 'opacity 0.3s ease';

                    // Forzar reflow para que la transición se aplique
                    requestAnimationFrame(() => {
                        contenedor.style.opacity = '1';
                    });

                    texto.textContent = 'Mostrar menos';
                    icono.classList.add('rotate-180');
                } else {
                    // Ocultar
                    contenedor.classList.add('hidden');
                    texto.textContent = `Ver ${totalOcultas} reseñas más`;
                    icono.classList.remove('rotate-180');

                    // Scroll suave al inicio de la lista para no perder la posición
                    document.getElementById('lista-resenas')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        </script>
    @endpush

    <style>
        #btn-ver-mas-icon {
            transition: transform 0.3s ease;
        }

        #btn-ver-mas-icon.rotate-180 {
            transform: rotate(180deg);
        }

        #resenas-ocultas {
            animation: fadeInResenas 0.3s ease-out;
        }

        @keyframes fadeInResenas {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
