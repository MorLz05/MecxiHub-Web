@extends('GestorMaestro.layouts.app')

@section('title', 'Detalles del Taller - MecxiHub')
@section('header-title', 'Detalles del Taller')
@section('header-subtitle', 'Información completa del establecimiento')

@section('content')
    <div class="mb-4">
        <a href="{{ route('gestor.talleres') }}"
            class="inline-flex items-center gap-2 text-brand-blue hover:text-brand-darkblue transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la lista</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="relative h-64 bg-gradient-to-r from-brand-darkblue via-brand-blue to-blue-800">
            @php
                $logoUrl = $tallerData['logo_url'] ?? null;
                $iniciales = '';
                foreach (preg_split('/\s+/', trim($tallerData['nombre'] ?? 'T')) as $p) {
                    if (!empty($p)) {
                        $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
                    }
                    if (mb_strlen($iniciales) >= 2) {
                        break;
                    }
                }
                if (empty($iniciales)) {
                    $iniciales = 'T';
                }
            @endphp

            {{-- Patrón de fondo --}}
            <div class="absolute inset-0 opacity-20"
                style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;">
            </div>

            {{-- Logo a la izquierda --}}
            <div class="absolute top-6 left-6 z-10">
                <div
                    class="w-24 h-24 rounded-2xl bg-white shadow-2xl border-2 border-white/30 flex items-center justify-center overflow-hidden p-2">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $tallerData['nombre'] }}"
                            class="w-full h-full object-contain">
                    @else
                        <div
                            class="w-full h-full rounded-xl bg-gradient-to-br from-brand-blue to-brand-darkblue flex items-center justify-center text-white font-black text-3xl">
                            {{ $iniciales }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                <div class="flex items-start justify-between gap-4 md:pl-28">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $tallerData['nombre'] ?? 'Sin nombre' }}</h1>
                        <p class="text-white/80 text-sm mt-1">{{ $tallerData['direccion'] ?? 'Sin dirección' }}</p>
                    </div>
                    <span
                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ isset($tallerData['verificado']) && $tallerData['verificado'] === true ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }}">
                        {{ isset($tallerData['verificado']) && $tallerData['verificado'] === true ? 'Verificado' : 'Pendiente' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información de contacto -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Información de Contacto</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="font-medium text-gray-900">{{ $tallerData['email'] ?? 'No disponible' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Teléfono</p>
                                <p class="font-medium text-gray-900">{{ $tallerData['telefono'] ?? 'No disponible' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Dirección</p>
                                <p class="font-medium text-gray-900">{{ $tallerData['direccion'] ?? 'No disponible' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del taller -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Detalles del Taller</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Horario</p>
                                @php
                                    $horario = $tallerData['horario'] ?? null;
                                    $tieneHorario = is_array($horario) && count($horario) > 0;
                                @endphp
                                @if ($tieneHorario)
                                    @php
                                        // Agrupar días con el mismo horario
                                        $diasOrden = [
                                            'lunes',
                                            'martes',
                                            'miercoles',
                                            'jueves',
                                            'viernes',
                                            'sabado',
                                            'domingo',
                                        ];
                                        $grupos = [];
                                        foreach ($diasOrden as $dia) {
                                            if (empty($horario[$dia])) {
                                                continue;
                                            }
                                            $key = json_encode($horario[$dia]);
                                            if (!isset($grupos[$key])) {
                                                $grupos[$key] = ['dias' => [], 'rangos' => $horario[$dia]];
                                            }
                                            $grupos[$key]['dias'][] = $dia;
                                        }
                                    @endphp
                                    <div class="space-y-1">
                                        @foreach ($grupos as $grupo)
                                            @php
                                                $primerDia = ucfirst($grupo['dias'][0]);
                                                $ultimoDia = ucfirst(end($grupo['dias']));
                                                $rangoDias =
                                                    count($grupo['dias']) === 1
                                                        ? $primerDia
                                                        : $primerDia . ' a ' . $ultimoDia;
                                                $rangoHoras = collect($grupo['rangos'])
                                                    ->map(
                                                        fn($r) => ($r['apertura'] ?? '') . ' - ' . ($r['cierre'] ?? ''),
                                                    )
                                                    ->implode(' / ');
                                            @endphp
                                            <p class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $rangoDias }}:</span>
                                                <span class="text-gray-700">{{ $rangoHoras }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="font-medium text-gray-900">No especificado</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Calificación</p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-medium text-gray-900">{{ number_format($tallerData['rating'] ?? 0, 1) }}</span>
                                    <span class="text-amber-400">
                                        <i class="fa-solid fa-star"></i>
                                    </span>
                                    <span class="text-sm text-gray-500">({{ $tallerData['resenas_count'] ?? 0 }}
                                        reseñas)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Plan</p>
                                <p class="font-medium text-gray-900">
                                    {{ $tallerData['plan']['nombre'] ?? 'Basico' }}
                                    @if (isset($tallerData['plan']['fecha_vencimiento']) && $tallerData['plan']['fecha_vencimiento'])
                                        <span class="text-xs text-gray-500 font-normal">
                                            (Vence:
                                            {{ \Carbon\Carbon::parse($tallerData['plan']['fecha_vencimiento'])->format('d/m/Y') }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Descripción</h3>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-gray-700">{{ $tallerData['descripcion'] ?? 'Sin descripción' }}</p>
                </div>
            </div>

            <!-- Ubicación -->
            @php
                $hayUbicacion = !empty($tallerData['latitud']) && !empty($tallerData['longitud']);
            @endphp

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Ubicación</h3>
                    @if ($hayUbicacion)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $tallerData['latitud'] }},{{ $tallerData['longitud'] }}"
                            target="_blank" rel="noopener"
                            class="text-xs text-brand-blue hover:text-brand-darkblue font-semibold inline-flex items-center gap-1">
                            <i class="fa-solid fa-route"></i> Cómo llegar
                        </a>
                    @endif
                </div>

                @if ($hayUbicacion)
                    <div class="rounded-xl overflow-hidden border border-gray-200 h-72 sm:h-80">
                        <div id="mapa-taller-detalle" class="w-full h-full bg-gray-100"></div>
                    </div>

                    <div class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-start gap-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dirección exacta</p>
                            <p class="font-medium text-gray-900 text-sm">{{ $tallerData['direccion'] ?? 'Sin dirección' }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1 font-mono">
                                {{ number_format((float) $tallerData['latitud'], 6) }},
                                {{ number_format((float) $tallerData['longitud'], 6) }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-amber-800 text-sm">Sin ubicación registrada</p>
                            <p class="text-amber-700 text-xs mt-0.5">
                                Este taller aún no ha registrado su ubicación exacta en el mapa.
                                La dirección capturada es: <strong>{{ $tallerData['direccion'] ?? 'N/A' }}</strong>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Especialidades -->
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Especialidades</h3>
                <div class="flex flex-wrap gap-2">
                    @if (isset($tallerData['especialidades']) &&
                            is_array($tallerData['especialidades']) &&
                            count($tallerData['especialidades']) > 0)
                        @foreach ($tallerData['especialidades'] as $especialidad)
                            <span class="px-3 py-1.5 bg-blue-50 text-brand-blue rounded-full text-sm font-medium">
                                {{ $especialidad }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">No hay especialidades registradas</p>
                    @endif
                </div>
            </div>

            <!-- Galería de imágenes -->
            @if (!empty($imagenes))
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Galería del establecimiento ({{ count($imagenes) }})
                    </h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                        @foreach ($imagenes as $img)
                            <a href="{{ $img['url'] }}" target="_blank" rel="noopener"
                                class="block aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100 hover:border-brand-blue hover:shadow-md transition group">
                                <img src="{{ $img['url'] }}" alt="{{ $img['nombre_original'] ?? 'Imagen' }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Información adicional -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                <div>
                    <p class="text-xs text-gray-500">ID del taller</p>
                    <p class="font-mono text-sm text-gray-700">{{ $tallerData['id'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Fecha de creación</p>
                    <p class="text-sm text-gray-700">
                        {{ isset($tallerData['fecha_creacion']) ? \Carbon\Carbon::parse($tallerData['fecha_creacion'])->format('d/m/Y H:i') : 'No disponible' }}
                    </p>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('gestor.talleres') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    @if (!empty($tallerData['latitud']) && !empty($tallerData['longitud']))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=marker&language=es&region=MX&loading=async&callback=initTallerDetalleMap"
            async defer></script>

        <script>
            let mapaDetalle = null;

            function initTallerDetalleMap() {
                console.log('✅ Google Maps listo para el detalle del taller');
                window.TALLER_MAPS_READY = true;
                renderMapaDetalle();
            }

            function renderMapaDetalle() {
                const contenedor = document.getElementById('mapa-taller-detalle');
                if (!contenedor || !window.TALLER_MAPS_READY) return;

                const pos = {
                    lat: {{ (float) $tallerData['latitud'] }},
                    lng: {{ (float) $tallerData['longitud'] }}
                };

                mapaDetalle = new google.maps.Map(contenedor, {
                    center: pos,
                    zoom: 16,
                    mapTypeControl: true,
                    streetViewControl: true,
                    fullscreenControl: true,
                    zoomControl: true,
                    mapId: 'DEMO_MAP_ID',
                });

                // Marcador avanzado
                const marker = new google.maps.marker.AdvancedMarkerElement({
                    map: mapaDetalle,
                    position: pos,
                    title: @json($tallerData['nombre'] ?? 'Taller'),
                });

                // InfoWindow con nombre y dirección
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                    <div style="font-family: sans-serif; min-width: 200px;">
                        <div style="font-weight: 700; color: #001B5E; font-size: 14px; margin-bottom: 4px;">
                            ${@json($tallerData['nombre'] ?? 'Taller')}
                        </div>
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">
                            ${@json($tallerData['direccion'] ?? '')}
                        </div>
                        @if (!empty($tallerData['telefono']))
                            <div style="font-size: 12px; color: #374151;">
                                <strong>Tel:</strong> {{ $tallerData['telefono'] }}
                            </div>
                        @endif
                    </div>
                `
                });

                // Abrir info window al hacer click
                marker.addListener('click', () => {
                    infoWindow.open(mapaDetalle, marker);
                });

                // Abrir por defecto
                infoWindow.open(mapaDetalle, marker);
            }

            // Redibujar en caso de que el mapa ya esté listo
            document.addEventListener('DOMContentLoaded', function() {
                if (window.TALLER_MAPS_READY) renderMapaDetalle();
            });
        </script>
    @endif
@endsection
