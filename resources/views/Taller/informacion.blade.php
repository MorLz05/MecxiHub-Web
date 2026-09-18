@extends('taller.layouts.app')

@section('title', 'Información del Taller - Panel Taller')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Título de página -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Información del taller</h1>
                <p class="text-gray-500 text-sm mt-1">Gestiona la información y servicios de tu taller.</p>
            </div>

            <!-- Mensajes de éxito/error -->
            @if (session('success'))
                <div
                    class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 flex items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('success_horario'))
                <div
                    class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 flex items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>
                    {{ session('success_horario') }}
                </div>
            @endif

            @if (session('error'))
                <div
                    class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 flex items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-xmark text-xl text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 shadow-sm">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">

                <!-- === INFORMACIÓN BÁSICA === -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div
                        class="border-b border-gray-200/50 px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-50/50 to-white">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#0066FF] to-[#001B5E] flex items-center justify-center shadow-sm">
                                    <i class="fa-solid fa-store text-white text-sm"></i>
                                </div>
                                Datos del taller
                            </h3>
                            <p class="text-sm text-gray-500 ml-10">Información general de tu taller.</p>
                        </div>
                        <!-- Badge de Plan -->
                        @if (empty($tallerData['plan']))
                            <div
                                class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm">
                                <i class="fa-solid fa-crown text-amber-600"></i>
                                <span class="text-sm text-amber-700 font-medium">Sin plan</span>
                                <span class="text-xs text-amber-600 hidden sm:inline">| Considera contratar un plan
                                    premium</span>
                            </div>
                        @else
                            <div
                                class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm">
                                <i class="fa-solid fa-crown text-blue-600"></i>
                                <span class="text-sm text-blue-700 font-medium">{{ $tallerData['plan'] }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-6">
                        <form action="{{ route('taller.informacion.update', $tallerId) }}" method="POST" class="space-y-5">
                            @csrf
                            @method('PUT')

                            <!-- Verificado Badge -->
                            <div
                                class="mb-6 flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50/80 to-white rounded-xl border border-blue-200/60">
                                <span class="text-sm font-semibold text-gray-700">Estado:</span>
                                @if ($tallerData['verificado'] ?? false)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 px-4 py-1.5 rounded-full text-sm font-semibold shadow-sm">
                                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                        Verificado
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 px-4 py-1.5 rounded-full text-sm font-semibold shadow-sm">
                                        <i class="fa-solid fa-clock text-amber-500"></i>
                                        En espera de verificación
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Nombre -->
                                <div class="md:col-span-2">
                                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre
                                        del taller <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-store"></i>
                                        </span>
                                        <input type="text" id="nombre" name="nombre"
                                            value="{{ old('nombre', $tallerData['nombre'] ?? '') }}" required
                                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="md:col-span-2">
                                    <label for="descripcion"
                                        class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción</label>
                                    <div class="relative flex items-start">
                                        <span class="absolute left-4 top-4 text-gray-400">
                                            <i class="fa-solid fa-align-left"></i>
                                        </span>
                                        <textarea id="descripcion" name="descripcion" rows="4"
                                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200 resize-none">{{ old('descripcion', $tallerData['descripcion'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <!-- Dirección (con Google Maps) -->
                                <div class="md:col-span-2">
                                    <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        Dirección <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </span>
                                        <input type="text" id="direccion" name="direccion" readonly
                                            value="{{ old('direccion', $tallerData['direccion'] ?? '') }}" required
                                            placeholder="Haz clic en el botón para elegir la ubicación en el mapa"
                                            class="w-full pl-11 pr-36 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200 cursor-pointer">

                                        <button type="button" id="btn-abrir-mapa"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#0066FF] to-[#001B5E] hover:from-[#0055DD] hover:to-[#001B5E] text-white rounded-lg font-semibold text-xs transition shadow-md shadow-blue-900/20">
                                            <i class="fa-solid fa-map-location-dot"></i>
                                            Elegir en mapa
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fa-solid fa-info-circle mr-1"></i>
                                        Se guardará la dirección tal como la devuelve Google Maps.
                                    </p>

                                    {{-- Campos ocultos para lat/lng --}}
                                    <input type="hidden" name="latitud" id="latitud"
                                        value="{{ old('latitud', $tallerData['latitud'] ?? '') }}">
                                    <input type="hidden" name="longitud" id="longitud"
                                        value="{{ old('longitud', $tallerData['longitud'] ?? '') }}">
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono
                                        <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-phone"></i>
                                        </span>
                                        <input type="number" id="telefono" name="telefono"
                                            value="{{ old('telefono', $tallerData['telefono'] ?? '') }}" required
                                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                    </div>
                                </div>

                                <!-- Email (NO EDITABLE) -->
                                <div class="md:col-span-2">
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo
                                        electrónico</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-envelope"></i>
                                        </span>
                                        <input type="email" id="email" name="email"
                                            value="{{ old('email', $tallerData['email'] ?? '') }}" disabled
                                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1"><i class="fa-solid fa-info-circle mr-1"></i>El
                                        correo está asociado a tu cuenta y no se puede modificar aquí.</p>
                                </div>
                            </div>

                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#0066FF] to-[#001B5E] hover:from-[#0055DD] hover:to-[#001B5E] text-white rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-900/20 hover:shadow-blue-900/30 hover:scale-[1.02]">
                                <i class="fa-solid fa-save"></i>
                                Guardar cambios
                            </button>
                        </form>
                    </div>
                </div>

                <!-- === IMÁGENES Y LOGO === -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div class="border-b border-gray-200/50 px-6 py-4 bg-gradient-to-r from-pink-50/50 to-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-br from-pink-500 to-pink-700 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-image text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Imágenes del taller</h3>
                                <p class="text-sm text-gray-500">Personaliza tu taller con un logo y fotos del
                                    establecimiento.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-8">

                        {{-- Mensajes específicos --}}
                        @if (session('success_logo'))
                            <div
                                class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-2 text-sm">
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                {{ session('success_logo') }}
                            </div>
                        @endif
                        @if (session('success_imagenes'))
                            <div
                                class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-2 text-sm">
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                {{ session('success_imagenes') }}
                            </div>
                        @endif

                        {{-- ========== LOGO ========== --}}
                        <div>
                            <h4
                                class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-crown text-[#FF8800]"></i>
                                Logo del taller
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Preview --}}
                                <div class="md:col-span-1">
                                    <div
                                        class="relative w-full aspect-square rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                        @if (!empty($tallerData['logo_url']))
                                            <img src="{{ $tallerData['logo_url'] }}" alt="Logo del taller"
                                                class="w-full h-full object-contain p-3 bg-white">
                                        @else
                                            <div class="text-center p-4">
                                                <i class="fa-solid fa-image text-4xl text-gray-300"></i>
                                                <p class="text-xs text-gray-400 mt-2">Sin logo</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="md:col-span-2 flex flex-col justify-center gap-3">
                                    <form action="{{ route('taller.informacion.logo.subir') }}" method="POST"
                                        enctype="multipart/form-data" id="form-logo">
                                        @csrf
                                        <label for="logo-input"
                                            class="flex flex-col items-center justify-center w-full py-6 rounded-xl border-2 border-dashed border-[#0066FF]/30 bg-blue-50/30 hover:bg-blue-50/60 cursor-pointer transition">
                                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-[#0066FF] mb-2"></i>
                                            <span class="text-sm font-semibold text-gray-700">Subir logo</span>
                                            <span class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP o SVG · Máx 5 MB</span>
                                            <input type="file" id="logo-input" name="logo" accept="image/*"
                                                class="hidden" onchange="document.getElementById('form-logo').submit()">
                                        </label>
                                    </form>

                                    @if (!empty($tallerData['logo_url']))
                                        <form action="{{ route('taller.informacion.logo.eliminar') }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar el logo del taller?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-semibold text-sm transition border border-red-200">
                                                <i class="fa-solid fa-trash"></i> Eliminar logo actual
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Separador --}}
                        <div class="border-t border-gray-200"></div>

                        {{-- ========== IMÁGENES DEL ESTABLECIMIENTO ========== --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4
                                    class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-store text-[#0066FF]"></i>
                                    Imágenes del establecimiento
                                    <span class="text-xs font-normal text-gray-400 normal-case">
                                        ({{ count($tallerData['imagenes'] ?? []) }})
                                    </span>
                                </h4>
                            </div>

                            {{-- Formulario de subida (VISIBLE y funcional) --}}
                            <form action="{{ route('taller.informacion.imagenes.subir') }}" method="POST"
                                enctype="multipart/form-data" id="form-imagenes" class="mb-4">
                                @csrf

                                <label for="imagenes-input"
                                    class="flex flex-col items-center justify-center w-full py-6 rounded-xl border-2 border-dashed border-[#0066FF]/30 bg-blue-50/30 hover:bg-blue-50/60 cursor-pointer transition">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-[#0066FF] mb-2"></i>
                                    <span class="text-sm font-semibold text-gray-700">Seleccionar imágenes</span>
                                    <span class="text-xs text-gray-500 mt-1">Puedes elegir varias a la vez · Máx 10 · 5 MB
                                        c/u</span>
                                    <input type="file" id="imagenes-input" name="imagenes[]" accept="image/*"
                                        multiple class="hidden">
                                </label>

                                {{-- Preview + botón submit que aparece cuando hay archivos --}}
                                <div id="preview-imagenes" class="hidden mt-3">
                                    <div id="preview-grid" class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-3"></div>
                                    <div class="flex items-center justify-between gap-3">
                                        <p id="preview-count" class="text-xs text-gray-500"></p>
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF8800] hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition shadow-md shadow-orange-500/20">
                                            <i class="fa-solid fa-cloud-arrow-up"></i> Subir imágenes
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Grid de imágenes guardadas --}}
                            @if (empty($tallerData['imagenes']))
                                <div class="text-center py-10 bg-gray-50 rounded-xl border border-gray-200 border-dashed">
                                    <i class="fa-solid fa-images text-4xl text-gray-300"></i>
                                    <p class="text-sm text-gray-500 mt-3 font-medium">Sin imágenes del establecimiento</p>
                                    <p class="text-xs text-gray-400">Sube fotos de tu taller para que los clientes lo
                                        conozcan</p>
                                </div>
                            @else
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach ($tallerData['imagenes'] as $img)
                                        <div
                                            class="group relative aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
                                            <img src="{{ $img['url'] }}"
                                                alt="{{ $img['nombre_original'] ?? 'Imagen' }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

                                            <div
                                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                                <a href="{{ $img['url'] }}" target="_blank"
                                                    class="w-9 h-9 rounded-lg bg-white/90 hover:bg-white flex items-center justify-center text-gray-800 transition"
                                                    title="Ver">
                                                    <i class="fa-solid fa-expand text-sm"></i>
                                                </a>
                                                <form
                                                    action="{{ route('taller.informacion.imagenes.eliminar', $img['id']) }}"
                                                    method="POST" onsubmit="return confirm('¿Eliminar esta imagen?')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-9 h-9 rounded-lg bg-red-500 hover:bg-red-600 flex items-center justify-center text-white transition"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                                <i class="fa-solid fa-info-circle"></i>
                                Máximo 10 imágenes a la vez · PNG, JPG o WEBP · 5 MB por imagen.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- === HORARIO DE ATENCIÓN === -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div class="border-b border-gray-200/50 px-6 py-4 bg-gradient-to-r from-orange-50/50 to-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#FF8800] to-orange-500 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-clock text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Horario de atención</h3>
                                <p class="text-sm text-gray-500">Define los horarios de atención de tu taller.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('taller.informacion.horario.update', $tallerId) }}" method="POST"
                            id="horarioForm">
                            @csrf
                            @method('PUT')

                            <!-- Indicador de validación general -->
                            <div id="validationSummary" class="hidden mb-4 p-4 rounded-xl border">
                                <!-- Se mostrarán los errores aquí -->
                            </div>

                            <div id="bloques-horario" class="space-y-4">
                                @if (!empty($bloques))
                                    @foreach ($bloques as $bloqueIndex => $bloque)
                                        <div class="bloque-item bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3 transition-all duration-200"
                                            data-bloque-index="{{ $bloqueIndex }}">
                                            <!-- Encabezado del bloque -->
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                                    <i class="fa-solid fa-layer-group text-[#0066FF]"></i>
                                                    Bloque de días
                                                </span>
                                                <button type="button"
                                                    class="eliminar-bloque text-red-500 hover:text-red-700 text-sm transition {{ count($bloques) <= 1 ? 'hidden' : '' }}">
                                                    <i class="fa-solid fa-trash"></i> Eliminar
                                                </button>
                                            </div>

                                            <!-- Selección de días -->
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($diasSemana as $dia)
                                                    <label
                                                        class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                                                        <input type="checkbox"
                                                            name="horario[bloques][{{ $bloqueIndex }}][dias][]"
                                                            value="{{ $dia }}"
                                                            class="dia-checkbox rounded border-gray-300 text-[#0066FF] focus:ring-[#0066FF]"
                                                            {{ in_array($dia, $bloque['dias'] ?? []) ? 'checked' : '' }}>
                                                        {{ ucfirst($dia) }}
                                                    </label>
                                                @endforeach
                                            </div>
                                            <!-- Mensaje de error para días -->
                                            <div
                                                class="error-dias text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                                                <i class="fa-solid fa-circle-exclamation"></i>
                                                Debes seleccionar al menos un día
                                            </div>

                                            <!-- Horarios -->
                                            <div class="horarios-container space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox"
                                                        class="es-24h rounded border-gray-300 text-[#FF8800] focus:ring-[#FF8800]"
                                                        id="es24h_{{ $bloqueIndex }}"
                                                        {{ $bloque['es24h'] ?? false ? 'checked' : '' }}>
                                                    <label for="es24h_{{ $bloqueIndex }}"
                                                        class="text-sm text-gray-600 cursor-pointer">24 horas</label>
                                                </div>

                                                <div class="rangos-container space-y-2"
                                                    style="{{ $bloque['es24h'] ?? false ? 'display: none;' : '' }}">
                                                    @if (!empty($bloque['rangos']))
                                                        @foreach ($bloque['rangos'] as $rangoIndex => $rango)
                                                            <div class="rango-item flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200/80"
                                                                data-rango-index="{{ $rangoIndex }}">
                                                                <input type="time"
                                                                    name="horario[bloques][{{ $bloqueIndex }}][horarios][rangos][{{ $rangoIndex }}][apertura]"
                                                                    value="{{ $rango['apertura'] ?? '09:00' }}"
                                                                    class="hora-apertura rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition"
                                                                    {{ $bloque['es24h'] ?? false ? 'disabled' : '' }}>
                                                                <span class="text-gray-400">-</span>
                                                                <input type="time"
                                                                    name="horario[bloques][{{ $bloqueIndex }}][horarios][rangos][{{ $rangoIndex }}][cierre]"
                                                                    value="{{ $rango['cierre'] ?? '18:00' }}"
                                                                    class="hora-cierre rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition"
                                                                    {{ $bloque['es24h'] ?? false ? 'disabled' : '' }}>
                                                                <button type="button"
                                                                    class="eliminar-rango text-red-400 hover:text-red-600 text-sm transition {{ count($bloque['rangos']) <= 1 ? 'hidden' : '' }}">
                                                                    <i class="fa-solid fa-times"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <!-- Rango por defecto -->
                                                        <div class="rango-item flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200/80"
                                                            data-rango-index="0">
                                                            <input type="time"
                                                                name="horario[bloques][{{ $bloqueIndex }}][horarios][rangos][0][apertura]"
                                                                value="09:00"
                                                                class="hora-apertura rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition"
                                                                {{ $bloque['es24h'] ?? false ? 'disabled' : '' }}>
                                                            <span class="text-gray-400">-</span>
                                                            <input type="time"
                                                                name="horario[bloques][{{ $bloqueIndex }}][horarios][rangos][0][cierre]"
                                                                value="18:00"
                                                                class="hora-cierre rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition"
                                                                {{ $bloque['es24h'] ?? false ? 'disabled' : '' }}>
                                                            <button type="button"
                                                                class="eliminar-rango text-red-400 hover:text-red-600 text-sm hidden transition">
                                                                <i class="fa-solid fa-times"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    <!-- Mensaje de error para horarios -->
                                                    <div
                                                        class="error-horarios text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                        El horario de cierre debe ser posterior al de apertura
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    class="agregar-rango text-sm text-[#FF8800] hover:text-orange-600 transition font-medium"
                                                    style="{{ $bloque['es24h'] ?? false ? 'display: none;' : '' }}">
                                                    <i class="fa-solid fa-plus-circle"></i> Agregar rango
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Bloque por defecto -->
                                    <div class="bloque-item bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3 transition-all duration-200"
                                        data-bloque-index="0">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fa-solid fa-layer-group text-[#0066FF]"></i>
                                                Bloque de días
                                            </span>
                                            <button type="button"
                                                class="eliminar-bloque text-red-500 hover:text-red-700 text-sm transition hidden">
                                                <i class="fa-solid fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($diasSemana as $dia)
                                                <label
                                                    class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                                                    <input type="checkbox" name="horario[bloques][0][dias][]"
                                                        value="{{ $dia }}"
                                                        class="dia-checkbox rounded border-gray-300 text-[#0066FF] focus:ring-[#0066FF]">
                                                    {{ ucfirst($dia) }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="error-dias text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            Debes seleccionar al menos un día
                                        </div>
                                        <div class="horarios-container space-y-2">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    class="es-24h rounded border-gray-300 text-[#FF8800] focus:ring-[#FF8800]"
                                                    id="es24h_default">
                                                <label for="es24h_default" class="text-sm text-gray-600 cursor-pointer">24
                                                    horas</label>
                                            </div>
                                            <div class="rangos-container space-y-2">
                                                <div class="rango-item flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200/80"
                                                    data-rango-index="0">
                                                    <input type="time"
                                                        name="horario[bloques][0][horarios][rangos][0][apertura]"
                                                        value="09:00"
                                                        class="hora-apertura rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                                                    <span class="text-gray-400">-</span>
                                                    <input type="time"
                                                        name="horario[bloques][0][horarios][rangos][0][cierre]"
                                                        value="18:00"
                                                        class="hora-cierre rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                                                    <button type="button"
                                                        class="eliminar-rango text-red-400 hover:text-red-600 text-sm hidden transition">
                                                        <i class="fa-solid fa-times"></i>
                                                    </button>
                                                </div>
                                                <div
                                                    class="error-horarios text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    El horario de cierre debe ser posterior al de apertura
                                                </div>
                                            </div>
                                            <button type="button"
                                                class="agregar-rango text-sm text-[#FF8800] hover:text-orange-600 transition font-medium">
                                                <i class="fa-solid fa-plus-circle"></i> Agregar rango
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" id="agregar-bloque"
                                class="mt-3 inline-flex items-center gap-2 px-4 py-2.5 bg-orange-50 hover:bg-orange-100 text-[#FF8800] rounded-xl font-semibold text-sm transition-all duration-200 border border-orange-200/50 hover:border-orange-300">
                                <i class="fa-solid fa-plus-circle"></i> Agregar bloque de días
                            </button>

                            <div
                                class="mt-4 text-sm text-gray-500 bg-blue-50/80 border border-blue-200/60 rounded-xl p-4 flex items-start gap-2">
                                <i class="fa-solid fa-info-circle text-[#0066FF] mt-0.5"></i>
                                <span>Crea bloques de días con el mismo horario. Puedes agregar múltiples rangos horarios
                                    por día.</span>
                            </div>

                            <button type="submit" id="guardar-horario"
                                class="mt-4 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF8800] to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-orange-500/30 hover:shadow-orange-500/40 hover:scale-[1.02]">
                                <i class="fa-solid fa-save"></i>
                                Guardar horario
                            </button>
                        </form>
                    </div>
                </div>

                <!-- === SERVICIOS === -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div class="border-b border-gray-200/50 px-6 py-4 bg-gradient-to-r from-purple-50/50 to-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-tools text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Servicios</h3>
                                <p class="text-sm text-gray-500">Servicios que ofrece tu taller.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Lista de servicios -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                            @if (empty($tallerData['servicios_count']))
                                <div
                                    class="col-span-full text-center py-12 bg-gray-50 rounded-xl border border-gray-200 border-dashed">
                                    <i class="fa-solid fa-tools text-4xl text-gray-300"></i>
                                    <p class="text-sm text-gray-500 mt-3 font-medium">Sin servicios registrados</p>
                                    <p class="text-xs text-gray-400">Agrega los servicios que ofreces</p>
                                </div>
                            @else
                                @foreach ($tallerData['servicios_count'] as $servicio)
                                    <div
                                        class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#0066FF] hover:shadow-md transition-all duration-200 group">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-sm font-medium text-gray-900 truncate">{{ $servicio['nombre'] ?? 'Sin nombre' }}</span>
                                                <span
                                                    class="text-xs px-2 py-0.5 rounded-full {{ $servicio['activo'] ?? true ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }}">
                                                    {{ $servicio['activo'] ?? true ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </div>
                                            @if (!empty($servicio['descripcion']))
                                                <p class="text-xs text-gray-500 truncate">{{ $servicio['descripcion'] }}
                                                </p>
                                            @endif
                                            @if (!empty($servicio['precio']))
                                                <p class="text-xs font-semibold text-gray-700">
                                                    <i class="fa-solid fa-dollar-sign text-[#FF8800]"></i>
                                                    {{ $servicio['precio'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <div
                                            class="flex items-center gap-1 ml-2 opacity-70 group-hover:opacity-100 transition">
                                            <form action="{{ route('taller.informacion.servicio.toggle', $tallerId) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="servicio_id"
                                                    value="{{ $servicio['id'] ?? '' }}">
                                                <input type="hidden" name="activo"
                                                    value="{{ $servicio['activo'] ?? true ? '0' : '1' }}">
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg {{ $servicio['activo'] ?? true ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' : 'bg-gray-200 text-gray-500 hover:bg-gray-300' }} transition flex items-center justify-center">
                                                    <i
                                                        class="fa-solid {{ $servicio['activo'] ?? true ? 'fa-toggle-on text-lg' : 'fa-toggle-off text-lg' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('taller.informacion.servicio.eliminar', $tallerId) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="servicio_id"
                                                    value="{{ $servicio['id'] ?? '' }}">
                                                <button type="submit"
                                                    onclick="return confirm('¿Eliminar este servicio?')"
                                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition flex items-center justify-center">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Agregar servicio -->
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-[#FF8800]"></i>
                                Agregar nuevo servicio
                            </h4>
                            <form action="{{ route('taller.informacion.servicio.agregar', $tallerId) }}" method="POST"
                                class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @csrf
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-400">
                                        <i class="fa-solid fa-tag text-sm"></i>
                                    </span>
                                    <input type="text" name="nombre" placeholder="Nombre del servicio" required
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-sm bg-gray-50/50 transition duration-200">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-400">
                                        <i class="fa-solid fa-align-left text-sm"></i>
                                    </span>
                                    <input type="text" name="descripcion" placeholder="Descripción (opcional)"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-sm bg-gray-50/50 transition duration-200">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-400">
                                        <i class="fa-solid fa-dollar-sign text-sm"></i>
                                    </span>
                                    <input type="text" name="precio" placeholder="Precio (opcional)"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-sm bg-gray-50/50 transition duration-200">
                                </div>
                                <div class="md:col-span-3">
                                    <button type="submit"
                                        class="w-full py-2.5 bg-gradient-to-r from-[#FF8800] to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-orange-500/20 hover:shadow-orange-500/30 hover:scale-[1.01] flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-plus"></i> Agregar servicio
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL DE GOOGLE MAPS --}}
    {{-- ============================================================ --}}
    <div id="modal-mapa" class="fixed inset-0 z-[9999] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarModalMapa()"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[85vh] flex flex-col overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0066FF] to-[#001B5E] flex items-center justify-center">
                            <i class="fa-solid fa-map-location-dot text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Elegir ubicación del taller</h3>
                            <p class="text-xs text-gray-500">Busca tu dirección y ajusta el marcador en el punto exacto.
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="cerrarModalMapa()"
                        class="w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-500 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Buscador (nueva API de Places) --}}
                <div class="px-6 py-3 border-b border-gray-100 flex-shrink-0">
                    <div id="buscador-mapa-container" class="w-full"></div>
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Busca calle, número, colonia, código postal o ciudad.
                    </p>
                </div>

                {{-- Mapa --}}
                <div class="flex-1 min-h-0 relative">
                    <div id="mapa-taller" class="w-full h-full bg-gray-100"></div>
                    <div id="mapa-loading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 border-3 border-[#0066FF] border-t-transparent rounded-full animate-spin">
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Cargando mapa...</p>
                        </div>
                    </div>
                </div>

                {{-- Dirección seleccionada + acciones --}}
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex-shrink-0">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Dirección seleccionada</p>
                            <p id="direccion-preview" class="text-sm text-gray-800 truncate font-medium">
                                Mueve el marcador o busca una dirección...
                            </p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="button" onclick="cerrarModalMapa()"
                                class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold text-sm transition">
                                Cancelar
                            </button>
                            <button type="button" id="btn-confirmar-mapa" disabled
                                class="px-5 py-2.5 bg-gradient-to-r from-[#0066FF] to-[#001B5E] hover:from-[#0055DD] hover:to-[#001B5E] text-white rounded-xl font-semibold text-sm transition shadow-md shadow-blue-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-check mr-1"></i> Usar esta dirección
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('bloques-horario');
            const validationSummary = document.getElementById('validationSummary');

            // === FUNCIÓN PARA ACTUALIZAR LA DISPONIBILIDAD DE DÍAS ===
            function actualizarDisponibilidadDias() {
                const todosLosDiasSeleccionados = new Set();
                document.querySelectorAll('.bloque-item').forEach(bloque => {
                    bloque.querySelectorAll('.dia-checkbox:checked').forEach(cb => {
                        todosLosDiasSeleccionados.add(cb.value);
                    });
                });

                document.querySelectorAll('.bloque-item').forEach(bloque => {
                    const checkboxes = bloque.querySelectorAll('.dia-checkbox');
                    checkboxes.forEach(cb => {
                        if (cb.checked) {
                            cb.disabled = false;
                            cb.closest('label').style.opacity = '1';
                            return;
                        }
                        if (todosLosDiasSeleccionados.has(cb.value)) {
                            cb.disabled = true;
                            cb.closest('label').style.opacity = '0.5';
                            cb.closest('label').title = 'Este día ya está asignado a otro bloque';
                        } else {
                            cb.disabled = false;
                            cb.closest('label').style.opacity = '1';
                            cb.closest('label').title = '';
                        }
                    });
                });
            }

            // === FUNCIÓN PARA VALIDAR UN BLOQUE EN TIEMPO REAL ===
            function validarBloque(bloque) {
                let esValido = true;
                let errores = [];

                const diasSeleccionados = bloque.querySelectorAll('.dia-checkbox:checked');
                const errorDias = bloque.querySelector('.error-dias');

                if (diasSeleccionados.length === 0) {
                    esValido = false;
                    errores.push('Selecciona al menos un día');
                    if (errorDias) errorDias.classList.remove('hidden');
                    bloque.querySelectorAll('.dia-checkbox').forEach(cb => cb.classList.add('error'));
                } else {
                    if (errorDias) errorDias.classList.add('hidden');
                    bloque.querySelectorAll('.dia-checkbox').forEach(cb => cb.classList.remove('error'));
                }

                const es24h = bloque.querySelector('.es-24h');
                const errorHorarios = bloque.querySelector('.error-horarios');

                if (!es24h || !es24h.checked) {
                    const rangos = bloque.querySelectorAll('.rango-item');
                    let todosLosRangosValidos = true;

                    rangos.forEach((rango, index) => {
                        const apertura = rango.querySelector('.hora-apertura');
                        const cierre = rango.querySelector('.hora-cierre');

                        if (apertura && cierre && apertura.value && cierre.value) {
                            const aperturaTime = apertura.value;
                            const cierreTime = cierre.value;

                            if (cierreTime <= aperturaTime) {
                                todosLosRangosValidos = false;
                                esValido = false;
                                errores.push(
                                    `Rango ${index + 1}: El cierre (${cierreTime}) debe ser después de la apertura (${aperturaTime})`
                                );

                                apertura.classList.add('error');
                                cierre.classList.add('error');
                                rango.classList.add('has-error');
                            } else {
                                apertura.classList.remove('error');
                                cierre.classList.remove('error');
                                apertura.classList.add('success');
                                cierre.classList.add('success');
                                rango.classList.remove('has-error');
                            }
                        }
                    });

                    if (todosLosRangosValidos) {
                        if (errorHorarios) errorHorarios.classList.add('hidden');
                    } else {
                        if (errorHorarios) errorHorarios.classList.remove('hidden');
                    }
                } else {
                    if (errorHorarios) errorHorarios.classList.add('hidden');
                    bloque.querySelectorAll('.hora-apertura, .hora-cierre').forEach(input => {
                        input.classList.remove('error', 'success');
                    });
                }

                if (esValido) {
                    bloque.classList.remove('has-error');
                    bloque.classList.add('is-valid');
                } else {
                    bloque.classList.remove('is-valid');
                    bloque.classList.add('has-error');
                }

                return {
                    esValido,
                    errores
                };
            }

            // === FUNCIÓN PARA VALIDAR TODOS LOS BLOQUES ===
            function validarTodosLosBloques() {
                let todosValidos = true;
                let todosErrores = [];

                document.querySelectorAll('.bloque-item').forEach((bloque, index) => {
                    const resultado = validarBloque(bloque);
                    if (!resultado.esValido) {
                        todosValidos = false;
                        todosErrores.push(`Bloque ${index + 1}: ${resultado.errores.join(', ')}`);
                    }
                });

                if (!todosValidos) {
                    validationSummary.classList.remove('hidden');
                    validationSummary.className = 'mb-4 p-4 rounded-xl border border-red-200 bg-red-50';
                    validationSummary.innerHTML = `
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-red-700">Hay errores en el horario:</p>
                                <ul class="list-disc list-inside text-sm text-red-600 mt-1">
                                    ${todosErrores.map(e => `<li>${e}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                } else {
                    const todosLosDias = new Set();
                    let hayDuplicados = false;
                    document.querySelectorAll('.bloque-item').forEach(bloque => {
                        bloque.querySelectorAll('.dia-checkbox:checked').forEach(cb => {
                            if (todosLosDias.has(cb.value)) {
                                hayDuplicados = true;
                            }
                            todosLosDias.add(cb.value);
                        });
                    });

                    if (hayDuplicados) {
                        validationSummary.classList.remove('hidden');
                        validationSummary.className = 'mb-4 p-4 rounded-xl border border-amber-200 bg-amber-50';
                        validationSummary.innerHTML = `
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                                <div>
                                    <p class="font-semibold text-amber-700">Días duplicados</p>
                                    <p class="text-sm text-amber-600">Un día no puede estar en dos bloques diferentes. Los días duplicados están deshabilitados.</p>
                                </div>
                            </div>
                        `;
                    } else {
                        validationSummary.classList.remove('hidden');
                        validationSummary.className = 'mb-4 p-4 rounded-xl border border-emerald-200 bg-emerald-50';
                        validationSummary.innerHTML = `
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <div>
                                    <p class="font-semibold text-emerald-700">Horario válido</p>
                                    <p class="text-sm text-emerald-600">Todos los bloques están correctamente configurados.</p>
                                </div>
                            </div>
                        `;
                    }
                }

                return todosValidos;
            }

            // === VALIDAR AL ENVIAR EL FORMULARIO DE HORARIO ===
            document.getElementById('horarioForm').addEventListener('submit', function(e) {
                const todosLosDias = new Set();
                let hayDuplicados = false;
                let diasDuplicados = [];

                document.querySelectorAll('.bloque-item').forEach(bloque => {
                    bloque.querySelectorAll('.dia-checkbox:checked').forEach(cb => {
                        if (todosLosDias.has(cb.value)) {
                            hayDuplicados = true;
                            if (!diasDuplicados.includes(cb.value)) {
                                diasDuplicados.push(cb.value);
                            }
                        }
                        todosLosDias.add(cb.value);
                    });
                });

                if (hayDuplicados) {
                    e.preventDefault();
                    alert(
                        `Error: Los siguientes días están duplicados en diferentes bloques:\n\n${diasDuplicados.map(d => `- ${d.charAt(0).toUpperCase() + d.slice(1)}`).join('\n')}\n\nCada día solo puede estar en un bloque.`
                    );
                    return false;
                }

                const esValido = validarTodosLosBloques();

                if (!esValido) {
                    e.preventDefault();
                    const primerError = document.querySelector(
                        '.has-error, .dia-checkbox.error, .hora-apertura.error, .hora-cierre.error');
                    if (primerError) {
                        const bloque = primerError.closest('.bloque-item');
                        if (bloque) {
                            bloque.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                    return false;
                }
            });

            // === EVENTOS PARA VALIDACIÓN EN TIEMPO REAL ===
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('dia-checkbox') ||
                    e.target.classList.contains('hora-apertura') ||
                    e.target.classList.contains('hora-cierre') ||
                    e.target.classList.contains('es-24h')) {

                    const bloque = e.target.closest('.bloque-item');
                    if (bloque) {
                        if (e.target.classList.contains('es-24h')) {
                            const rangosContainer = bloque.querySelector('.rangos-container');
                            const agregarRango = bloque.querySelector('.agregar-rango');

                            if (e.target.checked) {
                                if (rangosContainer) rangosContainer.style.display = 'none';
                                if (agregarRango) agregarRango.style.display = 'none';
                                bloque.querySelectorAll('.hora-apertura, .hora-cierre').forEach(input => {
                                    input.disabled = true;
                                    input.value = '00:00';
                                });
                            } else {
                                if (rangosContainer) rangosContainer.style.display = 'block';
                                if (agregarRango) agregarRango.style.display = 'inline-block';
                                bloque.querySelectorAll('.hora-apertura, .hora-cierre').forEach(input => {
                                    input.disabled = false;
                                    if (input.value === '00:00') {
                                        input.value = '09:00';
                                    }
                                });
                            }
                        }

                        if (e.target.classList.contains('dia-checkbox')) {
                            actualizarDisponibilidadDias();
                        }

                        validarBloque(bloque);
                        validarTodosLosBloques();
                    }
                }
            });

            // === FUNCIONES PARA AGREGAR/ELIMINAR BLOQUES Y RANGOS ===
            function obtenerPlantillaBloque() {
                const template = document.createElement('div');
                template.className =
                    'bloque-item bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3 transition-all duration-200';
                const index = document.querySelectorAll('.bloque-item').length;

                template.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-[#0066FF]"></i>
                            Bloque de días
                        </span>
                        <button type="button" class="eliminar-bloque text-red-500 hover:text-red-700 text-sm transition">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($diasSemana as $dia)
                            <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer" style="opacity: 1;">
                                <input type="checkbox" name="horario[bloques][${index}][dias][]" value="{{ $dia }}" class="dia-checkbox rounded border-gray-300 text-[#0066FF] focus:ring-[#0066FF]">
                                {{ ucfirst($dia) }}
                            </label>
                        @endforeach
                    </div>
                    <div class="error-dias text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Debes seleccionar al menos un día
                    </div>
                    <div class="horarios-container space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="es-24h rounded border-gray-300 text-[#FF8800] focus:ring-[#FF8800]" id="es24h_${index}_${Date.now()}">
                            <label for="es24h_${index}_${Date.now()}" class="text-sm text-gray-600 cursor-pointer">24 horas</label>
                        </div>
                        <div class="rangos-container space-y-2">
                            <div class="rango-item flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200/80" data-rango-index="0">
                                <input type="time" name="horario[bloques][${index}][horarios][rangos][0][apertura]" value="09:00" class="hora-apertura rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                                <span class="text-gray-400">-</span>
                                <input type="time" name="horario[bloques][${index}][horarios][rangos][0][cierre]" value="18:00" class="hora-cierre rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                                <button type="button" class="eliminar-rango text-red-400 hover:text-red-600 text-sm hidden transition">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                            <div class="error-horarios text-xs text-red-600 hidden mt-1 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                El horario de cierre debe ser posterior al de apertura
                            </div>
                        </div>
                        <button type="button" class="agregar-rango text-sm text-[#FF8800] hover:text-orange-600 transition font-medium">
                            <i class="fa-solid fa-plus-circle"></i> Agregar rango
                        </button>
                    </div>
                `;
                return template;
            }

            function obtenerPlantillaRango(bloqueIndex, rangoIndex, apertura = '09:00', cierre = '18:00') {
                const div = document.createElement('div');
                div.className =
                    'rango-item flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200/80';
                div.dataset.rangoIndex = rangoIndex;
                div.innerHTML = `
                    <input type="time" name="horario[bloques][${bloqueIndex}][horarios][rangos][${rangoIndex}][apertura]" value="${apertura}" class="hora-apertura rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                    <span class="text-gray-400">-</span>
                    <input type="time" name="horario[bloques][${bloqueIndex}][horarios][rangos][${rangoIndex}][cierre]" value="${cierre}" class="hora-cierre rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition">
                    <button type="button" class="eliminar-rango text-red-400 hover:text-red-600 text-sm transition">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;
                return div;
            }

            function reindexarBloques() {
                document.querySelectorAll('.bloque-item').forEach((bloque, index) => {
                    bloque.dataset.bloqueIndex = index;
                    bloque.querySelectorAll('[name*="[dias][]"]').forEach(input => {
                        input.name = `horario[bloques][${index}][dias][]`;
                    });
                    bloque.querySelectorAll('[name*="[horarios]"]').forEach(input => {
                        input.name = input.name.replace(/bloques\[\d+\]/, `bloques[${index}]`);
                    });
                });
            }

            function actualizarVisibilidadBotones() {
                const bloques = document.querySelectorAll('.bloque-item');
                bloques.forEach((bloque, index) => {
                    const eliminarBtn = bloque.querySelector('.eliminar-bloque');
                    if (eliminarBtn) {
                        eliminarBtn.classList.toggle('hidden', bloques.length <= 1);
                    }
                    const rangos = bloque.querySelectorAll('.rango-item');
                    rangos.forEach((rango, rIndex) => {
                        const eliminarRangoBtn = rango.querySelector('.eliminar-rango');
                        if (eliminarRangoBtn) {
                            eliminarRangoBtn.classList.toggle('hidden', rangos.length <= 1);
                        }
                    });
                });
            }

            const imagenesInput = document.getElementById('imagenes-input');
            const previewWrap = document.getElementById('preview-imagenes');
            const previewGrid = document.getElementById('preview-grid');
            const previewCount = document.getElementById('preview-count');

            if (imagenesInput && previewWrap && previewGrid) {
                imagenesInput.addEventListener('change', function() {
                    previewGrid.innerHTML = '';
                    const files = Array.from(this.files);

                    if (files.length === 0) {
                        previewWrap.classList.add('hidden');
                        return;
                    }

                    // Validaciones rápidas en cliente
                    const maxSize = 5 * 1024 * 1024;
                    const validFiles = [];
                    let errores = [];

                    files.forEach(f => {
                        if (f.size > maxSize) {
                            errores.push(`${f.name} excede 5 MB`);
                        } else if (!f.type.startsWith('image/')) {
                            errores.push(`${f.name} no es una imagen`);
                        } else {
                            validFiles.push(f);
                        }
                    });

                    if (validFiles.length > 10) {
                        errores.push('Máximo 10 imágenes por subida');
                        validFiles.splice(10);
                    }

                    if (errores.length > 0) {
                        alert('Algunos archivos fueron ignorados:\n\n' + errores.join('\n'));
                    }

                    // Reconstruir FileList solo con válidos
                    const dt = new DataTransfer();
                    validFiles.forEach(f => dt.items.add(f));
                    this.files = dt.files;

                    // Renderizar previews
                    validFiles.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const div = document.createElement('div');
                            div.className =
                                'relative aspect-square rounded-lg overflow-hidden border border-gray-200';
                            div.innerHTML =
                                `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                            previewGrid.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    });

                    previewCount.textContent = `${validFiles.length} imagen(es) lista(s) para subir`;
                    previewWrap.classList.remove('hidden');
                });
            }
            // Agregar bloque
            document.getElementById('agregar-bloque').addEventListener('click', function() {
                const nuevoBloque = obtenerPlantillaBloque();
                container.appendChild(nuevoBloque);
                reindexarBloques();
                actualizarVisibilidadBotones();
                actualizarDisponibilidadDias();
                validarTodosLosBloques();
            });

            // Eliminar bloque (delegación)
            document.addEventListener('click', function(e) {
                const eliminarBtn = e.target.closest('.eliminar-bloque');
                if (eliminarBtn) {
                    const bloque = eliminarBtn.closest('.bloque-item');
                    const bloques = document.querySelectorAll('.bloque-item');
                    if (bloques.length > 1) {
                        bloque.remove();
                        reindexarBloques();
                        actualizarVisibilidadBotones();
                        actualizarDisponibilidadDias();
                        validarTodosLosBloques();
                    } else {
                        alert('Debe haber al menos un bloque de horario.');
                    }
                }
            });

            // Agregar rango (delegación)
            document.addEventListener('click', function(e) {
                const agregarBtn = e.target.closest('.agregar-rango');
                if (!agregarBtn) return;

                const horariosContainer = agregarBtn.closest('.horarios-container');
                if (!horariosContainer) return;

                const rangosContainer = horariosContainer.querySelector('.rangos-container');
                if (!rangosContainer) return;

                const bloque = agregarBtn.closest('.bloque-item');
                const bloqueIndex = Array.from(document.querySelectorAll('.bloque-item')).indexOf(bloque);
                const rangoCount = rangosContainer.querySelectorAll('.rango-item').length;

                const nuevoRango = obtenerPlantillaRango(bloqueIndex, rangoCount);
                rangosContainer.appendChild(nuevoRango);

                reindexarBloques();
                actualizarVisibilidadBotones();
                validarTodosLosBloques();
            });

            // Eliminar rango (delegación)
            document.addEventListener('click', function(e) {
                const eliminarBtn = e.target.closest('.eliminar-rango');
                if (!eliminarBtn) return;

                const rango = eliminarBtn.closest('.rango-item');
                if (!rango) return;

                const rangosContainer = rango.closest('.rangos-container');
                if (!rangosContainer) return;

                const rangos = rangosContainer.querySelectorAll('.rango-item');
                if (rangos.length > 1) {
                    rango.remove();
                    reindexarBloques();
                    actualizarVisibilidadBotones();
                    validarTodosLosBloques();
                }
            });

            // Inicializar
            reindexarBloques();
            actualizarVisibilidadBotones();
            actualizarDisponibilidadDias();
            setTimeout(validarTodosLosBloques, 200);
        });
    </script>

    {{-- Google Maps API --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&language=es&region=MX&loading=async&callback=initGoogleMaps"
        async defer></script>

    <script>
        // Estado del modal
        let map;
        let marker;
        let geocoder;
        let placeAutocomplete; // Nueva instancia
        let direccionSeleccionada = {
            texto: '',
            lat: null,
            lng: null
        };

        // Callback que Google llama cuando la API está lista
        function initGoogleMaps() {
            console.log('✅ Google Maps API cargada');
            window.GOOGLE_MAPS_READY = true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modalMapa = document.getElementById('modal-mapa');
            const btnAbrirMapa = document.getElementById('btn-abrir-mapa');
            const btnConfirmar = document.getElementById('btn-confirmar-mapa');
            const inputDireccion = document.getElementById('direccion');
            const inputLatitud = document.getElementById('latitud');
            const inputLongitud = document.getElementById('longitud');
            const direccionPreview = document.getElementById('direccion-preview');

            // ---------- Abrir modal ----------
            btnAbrirMapa?.addEventListener('click', function() {
                if (!window.GOOGLE_MAPS_READY) {
                    alert('Google Maps aún se está cargando. Espera un momento e inténtalo de nuevo.');
                    return;
                }
                abrirModalMapa();
            });

            function abrirModalMapa() {
                modalMapa.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Inicializar el mapa la primera vez
                if (!map) {
                    inicializarMapa();
                } else {
                    // Redibujar por si el modal estaba oculto
                    google.maps.event.trigger(map, 'resize');

                    // Si ya había una dirección, re-centrar
                    if (direccionSeleccionada.lat && direccionSeleccionada.lng) {
                        const pos = {
                            lat: direccionSeleccionada.lat,
                            lng: direccionSeleccionada.lng
                        };
                        map.setCenter(pos);
                        marker.setPosition(pos);
                    }
                }
            }

            window.cerrarModalMapa = function() {
                modalMapa.classList.add('hidden');
                document.body.style.overflow = '';
            };

            // ---------- Inicializar mapa ----------
            function inicializarMapa() {
                geocoder = new google.maps.Geocoder();

                // Centro por defecto: CDMX
                const defaultCenter = {
                    lat: 19.4326,
                    lng: -99.1332
                };

                // Si ya hay coordenadas guardadas, usarlas
                const latGuardada = parseFloat(inputLatitud.value);
                const lngGuardada = parseFloat(inputLongitud.value);
                const hayCoords = !isNaN(latGuardada) && !isNaN(lngGuardada);

                // ------- Mapa -------
                map = new google.maps.Map(document.getElementById('mapa-taller'), {
                    center: hayCoords ? {
                        lat: latGuardada,
                        lng: lngGuardada
                    } : defaultCenter,
                    zoom: hayCoords ? 17 : 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                    mapId: 'DEMO_MAP_ID', // requerido para AdvancedMarkers
                });

                // ------- Marcador avanzado (nueva API) -------
                marker = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: hayCoords ? {
                        lat: latGuardada,
                        lng: lngGuardada
                    } : defaultCenter,
                    gmpDraggable: true,
                });

                // Click en el mapa → mover marcador
                map.addListener('click', function(e) {
                    marker.position = e.latLng;
                    reverseGeocode(e.latLng);
                });

                // Drag del marcador → reverse geocode
                marker.addListener('dragend', function(e) {
                    const pos = marker.position;
                    reverseGeocode({
                        lat: pos.lat,
                        lng: pos.lng
                    });
                });

                // ------- Autocomplete con la NUEVA API -------
                const contenedorBuscador = document.getElementById('buscador-mapa-container');
                contenedorBuscador.innerHTML = ''; // limpiar por si acaso

                try {
                    placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
                        componentRestrictions: {
                            country: 'mx'
                        },
                        requestedLanguage: 'es',
                        requestedRegion: 'mx',
                    });

                    // Estilo visual para que combine con el diseño
                    placeAutocomplete.style.width = '100%';
                    placeAutocomplete.style.setProperty('--gmp-input-border-radius', '12px');
                    placeAutocomplete.style.setProperty('--gmp-input-padding', '10px 12px');

                    contenedorBuscador.appendChild(placeAutocomplete);

                    // Evento de selección
                    placeAutocomplete.addEventListener('gmp-select', async ({
                        placePrediction
                    }) => {
                        console.log('🔍 Predicción seleccionada:', placePrediction);

                        try {
                            const place = placePrediction.toPlace();
                            await place.fetchFields({
                                fields: ['formattedAddress', 'location', 'displayName'],
                            });

                            console.log('📍 Lugar:', place);

                            if (!place.location) {
                                alert('No se pudo obtener la ubicación de esa dirección.');
                                return;
                            }

                            const lat = place.location.lat();
                            const lng = place.location.lng();
                            const direccion = place.formattedAddress || place.displayName || '';

                            map.setCenter({
                                lat,
                                lng
                            });
                            map.setZoom(17);
                            marker.position = {
                                lat,
                                lng
                            };

                            actualizarDireccion(direccion, lat, lng);
                        } catch (err) {
                            console.error('Error al procesar lugar:', err);
                            alert('Error al obtener los detalles de esa dirección.');
                        }
                    });
                } catch (err) {
                    console.error('❌ Error creando PlaceAutocompleteElement:', err);
                    contenedorBuscador.innerHTML = `
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                No se pudo cargar el buscador de direcciones. Intenta recargar la página.
            </div>
        `;
                }

                // Ocultar loading
                document.getElementById('mapa-loading').style.display = 'none';

                // Si ya había coords, mostrar la dirección actual
                if (hayCoords && inputDireccion.value) {
                    actualizarDireccion(inputDireccion.value, latGuardada, lngGuardada);
                }
            }

            // ---------- Reverse geocoding ----------
            function reverseGeocode(latLng) {
                // latLng puede venir como objeto {lat, lng} o como google.maps.LatLng
                const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
                const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;

                geocoder.geocode({
                    location: {
                        lat,
                        lng
                    }
                }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        actualizarDireccion(results[0].formatted_address, lat, lng);
                    } else {
                        console.warn('Reverse geocoding falló:', status);
                        actualizarDireccion(`${lat.toFixed(6)}, ${lng.toFixed(6)}`, lat, lng);
                    }
                });
            }

            // ---------- Actualizar estado interno ----------
            function actualizarDireccion(texto, lat, lng) {
                direccionSeleccionada = {
                    texto,
                    lat,
                    lng
                };
                direccionPreview.textContent = texto;
                btnConfirmar.disabled = false;
            }

            // ---------- Confirmar selección ----------
            btnConfirmar?.addEventListener('click', function() {
                if (!direccionSeleccionada.texto) return;

                inputDireccion.value = direccionSeleccionada.texto;
                inputLatitud.value = direccionSeleccionada.lat?.toFixed(6) || '';
                inputLongitud.value = direccionSeleccionada.lng?.toFixed(6) || '';

                cerrarModalMapa();
            });

            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modalMapa.classList.contains('hidden')) {
                    cerrarModalMapa();
                }
            });
        });
    </script>

    <style>
        /* Estilos para validación en tiempo real */
        .hora-apertura.error,
        .hora-cierre.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .hora-apertura.success,
        .hora-cierre.success {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
        }

        .bloque-item.has-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .bloque-item.is-valid {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
        }

        /* Estilos para inputs con error */
        .dia-checkbox.error {
            border-color: #ef4444 !important;
            outline: 2px solid #ef4444 !important;
        }
    </style>
@endsection
