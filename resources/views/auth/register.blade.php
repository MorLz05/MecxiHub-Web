@extends('conductor.layouts.app')

@section('title', 'Registro - MecxiHub')

@section('content')
    <!-- CONTENEDOR PRINCIPAL (Fondo blanco) -->
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-0 items-stretch">

            <!-- === COLUMNA IZQUIERDA: FORMULARIO REGISTRO === -->
            <div class="flex items-center justify-center p-8 sm:p-10">
                <div class="w-full max-w-md">

                    <!-- Título MecxiHub personalizado -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-black">
                            <span class="text-[#0039A6]">Mecxi</span><span class="text-[#FF6B00]">Hub</span>
                        </h2>
                        <p class="text-gray-500 text-sm mt-2">Únete y disfruta de todos los beneficios que tenemos para ti.
                        </p>
                    </div>

                    <!-- Mensaje de error general (si hay errores de sesión) -->
                    @if ($errors->any())
                        <div id="serverErrorContainer" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl hidden">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-circle-exclamation text-red-500 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-semibold text-red-800">Error en el registro</p>
                                    <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Selector de Rol -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Registrarme como</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" id="rolConductor"
                                class="py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-[#0039A6] bg-[#0039A6] text-white">
                                <i class="fa-solid fa-user"></i> Conductor
                            </button>
                            <button type="button" id="rolTaller"
                                class="py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-gray-200 bg-white text-gray-600 hover:border-[#0039A6] hover:text-[#0039A6]">
                                <i class="fa-solid fa-wrench"></i> Taller
                            </button>
                        </div>
                        <input type="hidden" name="rol" id="rolInput" value="conductor">
                    </div>

                    <!-- Formulario -->
                    <form action="{{ route('register') }}" method="POST" class="space-y-4" id="registerForm" novalidate>
                        @csrf
                        <input type="hidden" name="rol" id="rolHidden" value="conductor">

                        <!-- Campos Conductor -->
                        <div id="camposConductor">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre
                                    Completo</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-gray-400">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input type="text" id="name" name="name" required placeholder="Juan Pérez"
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition"
                                        value="{{ old('name') }}">
                                </div>
                                <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="nameError">
                                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                    <span>El nombre completo es requerido</span>
                                </div>
                            </div>
                        </div>

                        <!-- Campos Taller -->
                        <div id="camposTaller" class="hidden">
                            <div>
                                <label for="taller_nombre" class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre
                                    del Taller</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-gray-400">
                                        <i class="fa-solid fa-building"></i>
                                    </span>
                                    <input type="text" id="taller_nombre" name="taller_nombre"
                                        placeholder="Taller Mecánico Hernández"
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition"
                                        value="{{ old('taller_nombre') }}">
                                </div>
                                <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="tallerNombreError">
                                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                    <span>El nombre del taller es requerido</span>
                                </div>
                            </div>
                            {{-- <div>
                                <label for="taller_direccion" class="block text-sm font-semibold text-gray-700 mb-1.5">Dirección</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-gray-400">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>
                                    <input type="text" id="taller_direccion" name="taller_direccion" placeholder="Calle 123, Ciudad"
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition"
                                        value="{{ old('taller_direccion') }}">
                                </div>
                                <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="tallerDireccionError">
                                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                    <span>La dirección del taller es requerida</span>
                                </div>
                            </div>
                            <div>
                                <label for="taller_telefono" class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-gray-400">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input type="tel" id="taller_telefono" name="taller_telefono" placeholder="(96) 123 4567"
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition"
                                        value="{{ old('taller_telefono') }}">
                                </div>
                                <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="tallerTelefonoError">
                                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                    <span>El teléfono del taller es requerido</span>
                                </div>
                            </div> --}}
                        </div>

                        <!-- Email (común) -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo
                                Electrónico</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-gray-400">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="email" id="email" name="email" required
                                    placeholder="ejemplo@correo.com"
                                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition"
                                    value="{{ old('email') }}">
                            </div>
                            <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="emailError">
                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                <span>Ingresa un correo electrónico válido</span>
                            </div>
                            <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="emailDuplicateError">
                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                <span>Este correo electrónico ya está registrado</span>
                            </div>
                        </div>

                        <!-- Password (común) -->
                        <div>
                            <label for="password"
                                class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-gray-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" id="password" name="password" required
                                    placeholder="Mínimo 6 caracteres"
                                    class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                                <button type="button" id="togglePasswordRegister"
                                    class="absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <!-- Indicador de fuerza de contraseña -->
                            <div id="passwordStrength" class="mt-2 hidden">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div id="strengthBar" class="h-full bg-red-500 transition-all duration-300"
                                            style="width: 0%"></div>
                                    </div>
                                    <span id="strengthText"
                                        class="text-xs font-medium text-gray-600 min-w-[70px]">Débil</span>
                                </div>
                                <p id="passwordRequirement" class="text-xs text-gray-500 mt-1">
                                    <i class="fa-regular fa-circle mr-1" id="pwdLengthIcon"></i>
                                    Mínimo 6 caracteres
                                </p>
                            </div>
                            <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="passwordError">
                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                <span>La contraseña debe tener al menos 6 caracteres</span>
                            </div>
                        </div>

                        <!-- Confirm Password (común) -->
                        <div>
                            <label for="password_confirmation"
                                class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar Contraseña</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-gray-400">
                                    <i class="fa-solid fa-shield-check"></i>
                                </span>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    placeholder="Confirma tu contraseña"
                                    class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                                <button type="button" id="togglePasswordConfirm"
                                    class="absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Match Indicator -->
                        <div id="passwordMatchIndicator" class="hidden text-sm font-medium mt-1">
                            <span id="matchText" class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-green-500"></i>
                                Las contraseñas coinciden
                            </span>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="pt-2">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="terms" required id="termsCheckbox" required
                                    class="w-4 h-4 mt-0.5 rounded border-gray-300 text-[#0039A6] focus:ring-[#0039A6]">
                                <span class="text-sm text-gray-600 leading-tight">
                                    Acepto los <a href="#" id="termsLink"
                                        class="text-[#0039A6] font-semibold hover:underline">Términos y condiciones</a> y
                                    el <a href="#" id="privacyLink"
                                        class="text-[#0039A6] font-semibold hover:underline">Aviso de privacidad</a>.
                                </span>
                            </label>
                            <div class="error-message text-red-600 text-sm mt-1.5 hidden" id="termsError">
                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i>
                                <span>Debes aceptar los términos y condiciones</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn"
                            class="w-full mt-2 py-3.5 px-6 rounded-xl bg-[#FF6B00] hover:bg-orange-600 text-white font-semibold text-base shadow-lg shadow-orange-500/30 transition flex items-center justify-center gap-2">
                            <span>Crear Cuenta</span>
                            <i class="fa-solid fa-user-plus text-xs"></i>
                        </button>
                    </form>

                    <!-- Separador -->
                    <div class="flex items-center gap-4 my-6">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-xs text-gray-400 font-medium">o regístrate con</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <!-- Botones Social -->
                    <div class="space-y-3">
                        <button type="button" id="googleRegisterBtn"
                            class="w-full py-3 px-6 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition flex items-center justify-center gap-3 text-sm font-medium text-gray-700 disabled:opacity-60 disabled:cursor-not-allowed">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                                class="w-5 h-5">
                            <span id="googleRegisterBtnText">Continuar con Google</span>
                        </button>
                    </div>

                    <!-- Footer Link -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-500">
                            ¿Ya tienes cuenta?
                            <a href="{{ route('login') }}" class="text-[#FF6B00] font-bold hover:underline ml-1">Inicia
                                sesión aquí</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- === COLUMNA DERECHA: INFORMACIÓN === -->
            <div
                class="relative bg-[#002677] rounded-3xl overflow-hidden shadow-2xl min-h-[600px] flex items-center p-8 sm:p-10">
                <!-- Imagen de Fondo -->
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800&q=80"
                        alt="Mecánicos trabajando" class="w-full h-full object-cover opacity-30">
                </div>

                <!-- Overlay gradiente -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#002677]/90 to-[#001B5E]/90"></div>

                <!-- Onda Curva Naranja Decorativa -->
                <svg class="absolute top-0 right-0 h-full w-24 pointer-events-none z-0 text-[#FF6B00]"
                    viewBox="0 0 200 500" fill="none" preserveAspectRatio="none">
                    <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.4" />
                    <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.2" />
                </svg>

                <!-- Contenido -->
                <div class="relative z-10 text-white space-y-6 w-full">
                    <div>
                        <h2 class="text-3xl font-black leading-tight">
                            Únete a <span class="text-[#FF6B00]">MecxiHub</span> y descubre <br>
                            una nueva forma de cuidar tu auto
                        </h2>
                        <p class="text-blue-100/90 text-sm mt-2 leading-relaxed">
                            Regístrate gratis y accede a herramientas inteligentes, talleres verificados y asistencia
                            personalizada.
                        </p>
                    </div>

                    <!-- Beneficios principales -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-shield-halved text-[#FF6B00] text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Talleres verificados y calificados</p>
                                <p class="text-blue-200/80 text-sm">Solo talleres evaluados por otros conductores</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-list-check text-[#FF6B00] text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Gestión fácil de tus servicios y vehículos</p>
                                <p class="text-blue-200/80 text-sm">Solicita y da seguimiento en un solo lugar</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-bell text-[#FF6B00] text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Recordatorios y notificaciones de mantenimiento</p>
                                <p class="text-blue-200/80 text-sm">Mantén tu auto en óptimas condiciones</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600/50 flex items-center justify-center flex-shrink-0 border border-white/20">
                                <i class="fa-solid fa-robot text-[#FF6B00] text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Asistencia IA 24/7</p>
                                <p class="text-blue-200/80 text-sm">Resuelve tus dudas al instante con MexciBot</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA - Hablar con IA -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20 mt-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-blue-100/90">¿Necesitas ayuda?</p>
                                <p class="font-semibold text-white text-sm">Nuestro asistente IA está disponible 24/7 para
                                    apoyarte en lo que necesites.</p>
                            </div>
                            <a href="{{ route('asistente.ia') }}"
                                class="bg-[#FF6B00] hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition flex items-center gap-2 shadow-lg shadow-orange-500/30 whitespace-nowrap">
                                <i class="fa-solid fa-comment-dots"></i>
                                Hablar con IA
                            </a>
                        </div>
                    </div>

                    <!-- Grid de beneficios adicionales -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mb-1.5">
                                <i class="fa-solid fa-shield text-green-400 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-white">Seguridad garantizada</p>
                            <p class="text-xs text-blue-200/70">Protegemos tu información</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center mb-1.5">
                                <i class="fa-solid fa-clock text-blue-400 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-white">Ahorra tiempo</p>
                            <p class="text-xs text-blue-200/70">Gestiona tus servicios desde cualquier lugar</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center mb-1.5">
                                <i class="fa-solid fa-medal text-yellow-400 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-white">Calidad asegurada</p>
                            <p class="text-xs text-blue-200/70">Talleres evaluados por otros conductores</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center mb-1.5">
                                <i class="fa-solid fa-headset text-purple-400 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-white">Soporte al cliente</p>
                            <p class="text-xs text-blue-200/70">Ayuda en todo momento</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- === MODAL DE ERROR PERSONALIZADO === -->
    <div id="errorModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeInUp">
            <!-- Cabecera -->
            <div class="bg-[#0039A6] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Error en el registro</h3>
                    <p class="text-blue-200 text-sm">Por favor, corrige los siguientes problemas</p>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="p-6">
                <div id="errorMessages" class="space-y-2.5">
                    <!-- Los mensajes de error se inyectan aquí dinámicamente -->
                </div>

                <!-- Botón de acción -->
                <button type="button" id="closeErrorModalBtn"
                    class="mt-5 w-full py-3 px-4 rounded-xl bg-[#FF6B00] hover:bg-orange-600 text-white font-semibold transition flex items-center justify-center gap-2 shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-check"></i>
                    Entendido, lo corregiré
                </button>
            </div>
        </div>
    </div>

    <!-- === MODAL DE ÉXITO (Opcional) === -->
    <div id="successModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeInUp">
            <div class="bg-green-600 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">¡Registro exitoso!</h3>
                    <p class="text-green-200 text-sm">Tu cuenta ha sido creada correctamente</p>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-600 text-center">Serás redirigido automáticamente...</p>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div id="termsModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Términos y Condiciones</h3>
                <button type="button" id="closeTermsModal"
                    class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
            </div>
            <div class="p-6 text-gray-700 text-base leading-relaxed">
                <p class="mb-4"><strong>Última actualización:</strong> 5 de agosto de 2026</p>
                <h4 class="font-bold text-lg mt-6 mb-2">1. Aceptación de los Términos</h4>
                <p>Al utilizar MecxiHub, aceptas cumplir con estos Términos del Servicio. Si no estás de acuerdo, no
                    utilices nuestra plataforma.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">2. Descripción del Servicio</h4>
                <p>MecxiHub es una plataforma que conecta a conductores con soluciones automotrices, incluyendo servicios de
                    mantenimiento, reparación y asesoría.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">3. Registro y Cuenta</h4>
                <p>Eres responsable de mantener la confidencialidad de tu cuenta y contraseña. Notifícanos inmediatamente
                    sobre cualquier uso no autorizado.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">4. Conducta del Usuario</h4>
                <p>Te comprometes a utilizar el servicio de manera legal, respetuosa y sin infringir los derechos de
                    terceros.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">5. Propiedad Intelectual</h4>
                <p>Todo el contenido, marcas y materiales en MecxiHub son propiedad de MecxiHub o sus licenciantes y están
                    protegidos por derechos de autor.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">6. Limitación de Responsabilidad</h4>
                <p>MecxiHub no se hace responsable por daños indirectos derivados del uso de la plataforma.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">7. Modificaciones</h4>
                <p>Nos reservamos el derecho de actualizar estos términos en cualquier momento. Te notificaremos de cambios
                    significativos.</p>
            </div>
        </div>
    </div>

    <!-- Privacy Modal -->
    <div id="privacyModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Aviso de Privacidad</h3>
                <button type="button" id="closePrivacyModal"
                    class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
            </div>
            <div class="p-6 text-gray-700 text-base leading-relaxed">
                <p class="mb-4"><strong>Última actualización:</strong> 5 de agosto de 2026</p>
                <h4 class="font-bold text-lg mt-6 mb-2">1. Información que Recopilamos</h4>
                <p>Recopilamos información personal como nombre, correo electrónico, información de vehículo y datos de uso
                    para mejorar tu experiencia.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">2. Uso de la Información</h4>
                <p>Utilizamos tus datos para proporcionar servicios, comunicaciones, análisis y mejoras de la plataforma.
                </p>
                <h4 class="font-bold text-lg mt-6 mb-2">3. Seguridad</h4>
                <p>Implementamos medidas de seguridad para proteger tu información, pero ningún sistema es 100% seguro.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">4. Cookies</h4>
                <p>Utilizamos cookies para mejorar la funcionalidad y personalizar tu experiencia en MecxiHub.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">5. Terceros</h4>
                <p>No compartimos tu información personal con terceros sin tu consentimiento, excepto cuando sea necesario
                    para el servicio.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">6. Tus Derechos</h4>
                <p>Tienes derecho a acceder, modificar o eliminar tus datos personales en cualquier momento.</p>
                <h4 class="font-bold text-lg mt-6 mb-2">7. Contacto</h4>
                <p>Para preguntas sobre privacidad, contáctanos en <a href="mailto:privacidad@mecxihub.com"
                        class="text-[#0039A6] font-semibold">privacidad@mecxihub.com</a>.</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.3s ease-out forwards;
        }

        /* Estilos para errores en inputs */
        .input-error {
            border-color: #EF4444 !important;
            background-color: #FEF2F2 !important;
        }

        .input-error:focus {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        .input-success {
            border-color: #22C55E !important;
        }

        .input-success:focus {
            border-color: #22C55E !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Elementos del DOM ===
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const errorModal = document.getElementById('errorModal');
            const errorMessages = document.getElementById('errorMessages');
            const closeErrorModalBtn = document.getElementById('closeErrorModalBtn');

            // === Selector de Rol ===
            const rolConductor = document.getElementById('rolConductor');
            const rolTaller = document.getElementById('rolTaller');
            const camposConductor = document.getElementById('camposConductor');
            const camposTaller = document.getElementById('camposTaller');
            const rolHidden = document.getElementById('rolHidden');
            const rolInput = document.getElementById('rolInput');

            function setRol(rol) {
                if (rol === 'conductor') {
                    rolConductor.className =
                        'py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-[#0039A6] bg-[#0039A6] text-white';
                    rolTaller.className =
                        'py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-gray-200 bg-white text-gray-600 hover:border-[#0039A6] hover:text-[#0039A6]';
                    camposConductor.classList.remove('hidden');
                    camposTaller.classList.add('hidden');
                    rolHidden.value = 'conductor';
                    rolInput.value = 'conductor';

                    document.getElementById('taller_nombre').disabled = true;
                    /*
                                        document.getElementById('taller_direccion').disabled = true;
                                        document.getElementById('taller_telefono').disabled = true; */
                    document.getElementById('name').disabled = false;

                    // Resetear errores de taller
                    clearFieldError('tallerNombreError');
                    /* clearFieldError('tallerDireccionError');
                    clearFieldError('tallerTelefonoError'); */
                } else {
                    rolTaller.className =
                        'py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-[#0039A6] bg-[#0039A6] text-white';
                    rolConductor.className =
                        'py-3 px-4 rounded-xl border-2 font-semibold text-sm transition flex items-center justify-center gap-2 border-gray-200 bg-white text-gray-600 hover:border-[#0039A6] hover:text-[#0039A6]';
                    camposTaller.classList.remove('hidden');
                    camposConductor.classList.add('hidden');
                    rolHidden.value = 'taller';
                    rolInput.value = 'taller';

                    document.getElementById('name').disabled = true;
                    document.getElementById('taller_nombre').disabled = false;
                    /* document.getElementById('taller_direccion').disabled = false;
                    document.getElementById('taller_telefono').disabled = false; */

                    clearFieldError('nameError');
                }
            }

            rolConductor.addEventListener('click', function() {
                setRol('conductor');
            });
            rolTaller.addEventListener('click', function() {
                setRol('taller');
            });

            // === Toggle password visibility ===
            const togglePasswordRegister = document.getElementById('togglePasswordRegister');
            const passwordRegister = document.getElementById('password');

            togglePasswordRegister.addEventListener('click', function() {
                const type = passwordRegister.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordRegister.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmInput = document.getElementById('password_confirmation');

            togglePasswordConfirm.addEventListener('click', function() {
                const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // === Validación de contraseña en tiempo real ===
            const passwordInput = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            const matchIndicator = document.getElementById('passwordMatchIndicator');
            const matchText = document.getElementById('matchText');
            const passwordStrength = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const pwdLengthIcon = document.getElementById('pwdLengthIcon');

            function validatePasswordStrength() {
                const pwd = passwordInput.value;
                const length = pwd.length;

                if (length === 0) {
                    passwordStrength.classList.add('hidden');
                    return;
                }

                passwordStrength.classList.remove('hidden');

                // Actualizar icono de longitud
                if (length >= 6) {
                    pwdLengthIcon.className = 'fa-regular fa-circle-check text-green-500 mr-1';
                } else {
                    pwdLengthIcon.className = 'fa-regular fa-circle text-gray-400 mr-1';
                }

                // Barra de fuerza
                let width = 0;
                let color = '#EF4444';
                let label = 'Débil';

                if (length >= 6) {
                    width = 33;
                    color = '#F59E0B';
                    label = 'Media';
                }
                if (length >= 8) {
                    width = 66;
                    color = '#3B82F6';
                    label = 'Fuerte';
                }
                if (length >= 10 && /[A-Z]/.test(pwd) && /[0-9]/.test(pwd)) {
                    width = 100;
                    color = '#22C55E';
                    label = 'Muy fuerte';
                }

                strengthBar.style.width = width + '%';
                strengthBar.style.backgroundColor = color;
                strengthText.textContent = label;
                strengthText.style.color = color;

                // Validar límite mínimo
                if (length > 0 && length < 6) {
                    passwordInput.classList.add('input-error');
                    passwordInput.classList.remove('input-success');
                    showFieldError('passwordError');
                } else if (length >= 6) {
                    passwordInput.classList.remove('input-error');
                    passwordInput.classList.add('input-success');
                    hideFieldError('passwordError');
                } else {
                    passwordInput.classList.remove('input-error', 'input-success');
                    hideFieldError('passwordError');
                }

                validatePasswordMatch();
            }

            function validatePasswordMatch() {
                const pwd = passwordInput.value;
                const confirm = passwordConfirm.value;

                if (confirm.length > 0) {
                    matchIndicator.classList.remove('hidden');
                    if (pwd === confirm && pwd.length > 0) {
                        matchText.innerHTML =
                            '<i class="fa-solid fa-circle-check text-green-500"></i> Las contraseñas coinciden';
                        matchText.className = 'flex items-center gap-2 text-green-600';
                        passwordConfirm.classList.remove('input-error', 'border-red-500');
                        passwordConfirm.classList.add('input-success');
                    } else {
                        matchText.innerHTML =
                            '<i class="fa-solid fa-circle-xmark text-red-500"></i> Las contraseñas no coinciden';
                        matchText.className = 'flex items-center gap-2 text-red-600';
                        passwordConfirm.classList.remove('input-success');
                        passwordConfirm.classList.add('input-error');
                    }
                } else {
                    matchIndicator.classList.add('hidden');
                    passwordConfirm.classList.remove('input-error', 'input-success');
                }
            }

            passwordInput.addEventListener('input', validatePasswordStrength);
            passwordConfirm.addEventListener('input', validatePasswordMatch);

            // === Función para mostrar errores de campos ===
            function showFieldError(elementId) {
                const el = document.getElementById(elementId);
                if (el) el.classList.remove('hidden');
            }

            function hideFieldError(elementId) {
                const el = document.getElementById(elementId);
                if (el) el.classList.add('hidden');
            }

            function clearFieldError(elementId) {
                const el = document.getElementById(elementId);
                if (el) {
                    el.classList.add('hidden');
                    // Remover clase de error del input asociado
                    const inputId = elementId.replace('Error', '');
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.classList.remove('input-error', 'input-success');
                    }
                }
            }

            function setInputError(inputId, errorId, message) {
                const input = document.getElementById(inputId);
                const errorEl = document.getElementById(errorId);
                if (input) {
                    input.classList.add('input-error');
                    input.classList.remove('input-success');
                }
                if (errorEl) {
                    const span = errorEl.querySelector('span');
                    if (span) span.textContent = message;
                    errorEl.classList.remove('hidden');
                }
            }

            function clearInputError(inputId, errorId) {
                const input = document.getElementById(inputId);
                const errorEl = document.getElementById(errorId);
                if (input) {
                    input.classList.remove('input-error');
                }
                if (errorEl) {
                    errorEl.classList.add('hidden');
                }
            }

            // === Validación del formulario antes de enviar ===
            function validateForm() {
                let hasErrors = false;
                const errors = [];
                const rol = rolHidden.value;

                // Validar nombre (conductor)
                if (rol === 'conductor') {
                    const name = document.getElementById('name').value.trim();
                    if (!name) {
                        setInputError('name', 'nameError', 'El nombre completo es requerido');
                        hasErrors = true;
                        errors.push('El nombre completo es requerido');
                    } else {
                        clearInputError('name', 'nameError');
                    }
                }

                // Validar campos de taller
                if (rol === 'taller') {
                    const tallerNombre = document.getElementById('taller_nombre').value.trim();
                    if (!tallerNombre) {
                        setInputError('taller_nombre', 'tallerNombreError', 'El nombre del taller es requerido');
                        hasErrors = true;
                        errors.push('El nombre del taller es requerido');
                    } else {
                        clearInputError('taller_nombre', 'tallerNombreError');
                    }

                    /*  const tallerDireccion = document.getElementById('taller_direccion').value.trim();
                     if (!tallerDireccion) {
                         setInputError('taller_direccion', 'tallerDireccionError', 'La dirección del taller es requerida');
                         hasErrors = true;
                         errors.push('La dirección del taller es requerida');
                     } else {
                         clearInputError('taller_direccion', 'tallerDireccionError');
                     }

                     const tallerTelefono = document.getElementById('taller_telefono').value.trim();
                     if (!tallerTelefono) {
                         setInputError('taller_telefono', 'tallerTelefonoError', 'El teléfono del taller es requerido');
                         hasErrors = true;
                         errors.push('El teléfono del taller es requerido');
                     } else {
                         clearInputError('taller_telefono', 'tallerTelefonoError');
                     } */
                }

                // Validar email
                const email = document.getElementById('email').value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email) {
                    setInputError('email', 'emailError', 'El correo electrónico es requerido');
                    hasErrors = true;
                    errors.push('El correo electrónico es requerido');
                } else if (!emailRegex.test(email)) {
                    setInputError('email', 'emailError', 'Ingresa un correo electrónico válido');
                    hasErrors = true;
                    errors.push('Ingresa un correo electrónico válido');
                } else {
                    clearInputError('email', 'emailError');
                }

                // Validar contraseña
                const password = passwordInput.value;
                if (!password) {
                    setInputError('password', 'passwordError', 'La contraseña es requerida');
                    hasErrors = true;
                    errors.push('La contraseña es requerida');
                } else if (password.length < 6) {
                    setInputError('password', 'passwordError', 'La contraseña debe tener al menos 6 caracteres');
                    hasErrors = true;
                    errors.push('La contraseña debe tener al menos 6 caracteres');
                } else {
                    clearInputError('password', 'passwordError');
                }

                // Validar confirmación de contraseña
                const confirm = passwordConfirm.value;
                if (confirm && password !== confirm) {
                    hasErrors = true;
                    errors.push('Las contraseñas no coinciden');
                }

                // Validar términos
                const terms = document.getElementById('termsCheckbox');
                if (!terms.checked) {
                    const termsError = document.getElementById('termsError');
                    termsError.classList.remove('hidden');
                    hasErrors = true;
                    errors.push('Debes aceptar los términos y condiciones');
                } else {
                    document.getElementById('termsError').classList.add('hidden');
                }

                if (hasErrors) {
                    showErrorModal(errors);
                    return false;
                }

                return true;
            }

            // === Mostrar Modal de Error ===
            function showErrorModal(errors) {
                errorMessages.innerHTML = '';
                errors.forEach(function(msg) {
                    const div = document.createElement('div');
                    div.className = 'flex items-start gap-3 p-3 bg-red-50 rounded-xl border border-red-100';
                    div.innerHTML = `
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm mt-0.5"></i>
                        <span class="text-sm text-gray-700">${msg}</span>
                    `;
                    errorMessages.appendChild(div);
                });
                errorModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            // === Cerrar Modal de Error ===
            function closeErrorModal() {
                errorModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            closeErrorModalBtn.addEventListener('click', closeErrorModal);
            errorModal.addEventListener('click', function(e) {
                if (e.target === errorModal) closeErrorModal();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !errorModal.classList.contains('hidden')) {
                    closeErrorModal();
                }
            });

            // === Envío del formulario con validación ===
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    // Scroll al inicio del formulario
                    form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });

            // === Manejo de errores del servidor (para email duplicado, etc.) ===
            // Capturar errores de validación de Laravel desde la sesión
            @if ($errors->any())
                (function() {
                    const serverErrors = [];
                    @foreach ($errors->all() as $error)
                        serverErrors.push('{{ $error }}');
                    @endforeach

                    // Limpiar mensajes de error de campos específicos
                    @if ($errors->has('email'))
                        const emailInput = document.getElementById('email');
                        if (emailInput) {
                            emailInput.classList.add('input-error');
                            const errorEl = document.getElementById('emailDuplicateError');
                            if (errorEl) {
                                errorEl.classList.remove('hidden');
                                const span = errorEl.querySelector('span');
                                if (span) span.textContent = 'Este correo electrónico ya está registrado';
                            }
                        }
                    @endif

                    if (serverErrors.length > 0) {
                        // Mostrar solo errores específicos en el modal
                        const filteredErrors = serverErrors.filter(err =>
                            !err.includes('email') || // Los emails duplicados ya se muestran en el campo
                            true
                        );

                        if (filteredErrors.length > 0) {
                            // Si hay errores de email duplicado, no mostrar duplicados en el modal
                            const hasEmailDuplicate = serverErrors.some(err => err.includes('correo') && err
                                .includes('registrado'));
                            const errorsToShow = hasEmailDuplicate ?
                                serverErrors.filter(err => !err.includes('correo') || !err.includes(
                                    'registrado')) :
                                serverErrors;

                            if (errorsToShow.length > 0) {
                                setTimeout(function() {
                                    showErrorModal(errorsToShow);
                                }, 300);
                            }
                        }
                    }
                })();
            @endif

            // === Limpiar error de email duplicado al escribir ===
            document.getElementById('email').addEventListener('input', function() {
                const duplicateError = document.getElementById('emailDuplicateError');
                if (!duplicateError.classList.contains('hidden')) {
                    duplicateError.classList.add('hidden');
                }
                if (this.classList.contains('input-error')) {
                    this.classList.remove('input-error');
                }
            });

            // === Limpiar error de términos al hacer click ===
            document.getElementById('termsCheckbox').addEventListener('change', function() {
                const termsError = document.getElementById('termsError');
                if (this.checked) {
                    termsError.classList.add('hidden');
                }
            });

            // === Modales de Términos y Privacidad ===
            const termsModal = document.getElementById('termsModal');
            const termsLink = document.getElementById('termsLink');
            const closeTermsModal = document.getElementById('closeTermsModal');

            termsLink.addEventListener('click', function(e) {
                e.preventDefault();
                termsModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            closeTermsModal.addEventListener('click', function() {
                termsModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });

            termsModal.addEventListener('click', function(e) {
                if (e.target === termsModal) {
                    termsModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });

            const privacyModal = document.getElementById('privacyModal');
            const privacyLink = document.getElementById('privacyLink');
            const closePrivacyModal = document.getElementById('closePrivacyModal');

            privacyLink.addEventListener('click', function(e) {
                e.preventDefault();
                privacyModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            closePrivacyModal.addEventListener('click', function() {
                privacyModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });

            privacyModal.addEventListener('click', function(e) {
                if (e.target === privacyModal) {
                    privacyModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });

            // Cerrar modales con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!termsModal.classList.contains('hidden')) {
                        termsModal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                    if (!privacyModal.classList.contains('hidden')) {
                        privacyModal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                }
            });

            // === Mostrar errores de validación del servidor (si no se mostraron antes) ===
            // Si el formulario fue enviado y hay errores, mostrarlos
            @if ($errors->any() && !$errors->has('email'))
                // Ya se maneja arriba
            @endif
        });
    </script>

    <!-- ===== FIREBASE JS SDK (Google Sign-In) ===== -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>

    <script>
        const firebaseConfig = {
            apiKey: "{{ config('firebase_client.api_key') }}",
            authDomain: "{{ config('firebase_client.auth_domain') }}",
            projectId: "{{ config('firebase_client.project_id') }}",
            storageBucket: "{{ config('firebase_client.storage_bucket') }}",
            messagingSenderId: "{{ config('firebase_client.messaging_sender_id') }}",
            appId: "{{ config('firebase_client.app_id') }}"
        };

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const googleRegisterBtn = document.getElementById('googleRegisterBtn');
        const googleRegisterBtnText = document.getElementById('googleRegisterBtnText');

        googleRegisterBtn.addEventListener('click', async function() {
            // Validar términos primero (es requisito del registro)
            const termsCheckbox = document.getElementById('termsCheckbox');
            if (termsCheckbox && !termsCheckbox.checked) {
                alert('Debes aceptar los términos y condiciones para registrarte.');
                return;
            }

            const originalText = googleRegisterBtnText.textContent;
            googleRegisterBtn.disabled = true;
            googleRegisterBtnText.textContent = 'Conectando...';

            // Rol seleccionado actualmente
            const rolSeleccionado = document.getElementById('rolHidden')?.value || 'conductor';

            try {
                const provider = new firebase.auth.GoogleAuthProvider();
                provider.setCustomParameters({
                    prompt: 'select_account'
                });

                const result = await firebase.auth().signInWithPopup(provider);
                const idToken = await result.user.getIdToken();

                const response = await fetch("{{ route('auth.google') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        id_token: idToken,
                        rol: rolSeleccionado
                    })
                });

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'No se pudo completar el registro con Google.');
                    googleRegisterBtn.disabled = false;
                    googleRegisterBtnText.textContent = originalText;
                }
            } catch (error) {
                console.error('Error Google Sign-In:', error);

                let msg = 'Error al registrarte con Google.';
                if (error.code === 'auth/popup-closed-by-user') {
                    msg = 'Cerraste la ventana de Google. Intenta de nuevo.';
                } else if (error.code === 'auth/popup-blocked') {
                    msg = 'El navegador bloqueó la ventana emergente. Permite popups para este sitio.';
                } else if (error.code === 'auth/cancelled-popup-request') {
                    msg = null;
                }

                if (msg) alert(msg);
                googleRegisterBtn.disabled = false;
                googleRegisterBtnText.textContent = originalText;
            }
        });
    </script>
@endsection
