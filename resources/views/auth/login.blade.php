@extends('conductor.layouts.app')

@section('title', 'Iniciar Sesión - MecxiHub')

@section('content')
    <!-- CONTENEDOR PRINCIPAL (Fondo blanco) -->
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-0 items-stretch">

            <!-- === COLUMNA IZQUIERDA: FORMULARIO LOGIN (Sin borde ni sombra, integrado al fondo) === -->
            <div class="flex items-center justify-center p-8 sm:p-10">
                <div class="w-full max-w-sm">

                    <!-- Título MecxiHub personalizado -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-black">
                            <span class="text-[#0039A6]">Mecxi</span><span class="text-[#FF6B00]">Hub</span>
                        </h2>
                        <p class="text-gray-500 text-sm mt-2">Accede a tu cuenta para gestionar tus servicios y mucho más.
                        </p>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulario -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo
                                Electrónico</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-gray-400">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="email" id="email" name="email" required
                                    placeholder="ejemplo@correo.com"
                                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña</label>
                            </div>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-gray-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" id="password" name="password" required
                                    placeholder="Ingresa tu contraseña"
                                    class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                                <button type="button" id="togglePasswordLogin"
                                    class="absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember me & Forgot password -->
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 rounded border-gray-300 text-[#0039A6] focus:ring-[#0039A6]">
                                <span class="text-sm text-gray-600 font-medium">Recordarme</span>
                            </label>
                            <a href="#" class="text-sm font-semibold text-[#0039A6] hover:underline">¿Olvidaste tu
                                contraseña?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-3.5 px-6 rounded-xl bg-[#0039A6] hover:bg-[#002B80] text-white font-semibold text-base shadow-lg shadow-blue-900/20 transition flex items-center justify-center gap-2">
                            <span>Iniciar Sesión</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>

                    <!-- Separador -->
                    <div class="flex items-center gap-4 my-6">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-xs text-gray-400 font-medium">o continúa con</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <!-- Botones Social -->
                    <div class="space-y-3">
                        <button type="button" id="googleLoginBtn"
                            class="w-full py-3 px-6 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition flex items-center justify-center gap-3 text-sm font-medium text-gray-700 disabled:opacity-60 disabled:cursor-not-allowed">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                                class="w-5 h-5">
                            <span id="googleLoginBtnText">Continuar con Google</span>
                        </button>
                    </div>

                    <!-- Footer Link -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-500">
                            ¿No tienes cuenta?
                            <a href="{{ route('register') }}"
                                class="text-[#FF6B00] font-bold hover:underline ml-1">Regístrate ahora</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- === COLUMNA DERECHA: INFORMACIÓN (Fondo azul con imagen) === -->
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
                <svg class="absolute top-0 right-0 h-full w-24 pointer-events-none z-0 text-[#FF6B00]" viewBox="0 0 200 500"
                    fill="none" preserveAspectRatio="none">
                    <path d="M 120,0 C 60,150 180,350 100,500 L 200,500 L 200,0 Z" fill="currentColor" opacity="0.4" />
                    <path d="M 150,0 C 100,180 190,320 140,500 L 200,500 L 200,0 Z" fill="#FF8800" opacity="0.2" />
                </svg>

                <!-- Contenido -->
                <div class="relative z-10 text-white space-y-6 w-full">

                    <!-- Título -->
                    <div>
                        <h2 class="text-3xl font-black leading-tight">
                            Tu auto, en las <br>
                            <span class="text-[#FF6B00]">mejores manos</span>
                        </h2>
                        <p class="text-blue-100/90 text-sm mt-2 leading-relaxed">
                            Encuentra talleres confiables, solicita servicios y recibe ayuda con nuestra IA.
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
                                <p class="font-semibold text-white">Gestión fácil de tus servicios</p>
                                <p class="text-blue-200/80 text-sm">Solicita y da seguimiento en un solo lugar</p>
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
                                <p class="font-semibold text-white text-sm">Pregunta a nuestro asistente IA y obtén
                                    respuestas al instante.</p>
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
                            <p class="text-xs text-blue-200/70">Todo en un solo lugar</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center mb-1.5">
                                <i class="fa-solid fa-medal text-yellow-400 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-white">Calidad asegurada</p>
                            <p class="text-xs text-blue-200/70">Talleres evaluados</p>
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
        <!-- ===== MODAL PARA RESTABLECER CONTRASEÑA ===== -->
        <div id="passwordResetModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
                <!-- Botón cerrar -->
                <button onclick="closePasswordResetModal()"
                    class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>

                <!-- Contenido del modal -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-key text-2xl text-[#0039A6]"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">¿Olvidaste tu contraseña?</h3>
                    <p class="text-sm text-gray-500 mt-1">Ingresa tu correo y te enviaremos un enlace para restablecerla.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form id="passwordResetForm" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="reset_email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo
                            Electrónico</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" id="reset_email" name="email" required
                                placeholder="ejemplo@correo.com"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-6 rounded-xl bg-[#0039A6] hover:bg-[#002B80] text-white font-semibold text-base shadow-lg shadow-blue-900/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Enviar enlace</span>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <button onclick="closePasswordResetModal()" class="text-sm text-gray-500 hover:text-gray-700">
                        Volver al inicio de sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility for login
            const togglePasswordLogin = document.getElementById('togglePasswordLogin');
            const passwordLogin = document.getElementById('password');

            if (togglePasswordLogin && passwordLogin) {
                togglePasswordLogin.addEventListener('click', function() {
                    const type = passwordLogin.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordLogin.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        });

        // Funciones para el modal
        function openPasswordResetModal() {
            document.getElementById('passwordResetModal').classList.remove('hidden');
            document.getElementById('passwordResetModal').classList.add('flex');
        }

        function closePasswordResetModal() {
            document.getElementById('passwordResetModal').classList.add('hidden');
            document.getElementById('passwordResetModal').classList.remove('flex');
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePasswordResetModal();
            }
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('passwordResetModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordResetModal();
            }
        });

        // Abrir modal al hacer clic en "¿Olvidaste tu contraseña?"
        document.addEventListener('DOMContentLoaded', function() {
            const forgotLink = document.querySelector('a[href="#"]');
            if (forgotLink) {
                forgotLink.setAttribute('href', 'javascript:void(0)');
                forgotLink.setAttribute('onclick', 'openPasswordResetModal()');
            }
        });

        // Si hay errores, abrir el modal automáticamente
        @if ($errors->has('email'))
            document.addEventListener('DOMContentLoaded', function() {
                openPasswordResetModal();
            });
        @endif
    </script>

    <!-- ===== FIREBASE JS SDK (Google Sign-In) ===== -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>

    <script>
        // Configuración de Firebase (viene del .env vía config)
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

        const googleLoginBtn = document.getElementById('googleLoginBtn');
        const googleLoginBtnText = document.getElementById('googleLoginBtnText');

        googleLoginBtn.addEventListener('click', async function() {
            const originalText = googleLoginBtnText.textContent;
            googleLoginBtn.disabled = true;
            googleLoginBtnText.textContent = 'Conectando...';

            try {
                const provider = new firebase.auth.GoogleAuthProvider();
                provider.setCustomParameters({
                    prompt: 'select_account'
                });

                const result = await firebase.auth().signInWithPopup(provider);
                const idToken = await result.user.getIdToken();

                // Enviar al backend
                const response = await fetch("{{ route('auth.google') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        id_token: idToken,
                        rol: 'conductor' // En login siempre es conductor; el backend respeta el rol existente
                    })
                });

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'No se pudo iniciar sesión con Google.');
                    googleLoginBtn.disabled = false;
                    googleLoginBtnText.textContent = originalText;
                }
            } catch (error) {
                console.error('Error Google Sign-In:', error);

                let msg = 'Error al iniciar sesión con Google.';
                if (error.code === 'auth/popup-closed-by-user') {
                    msg = 'Cerraste la ventana de Google. Intenta de nuevo.';
                } else if (error.code === 'auth/popup-blocked') {
                    msg = 'El navegador bloqueó la ventana emergente. Permite popups para este sitio.';
                } else if (error.code === 'auth/cancelled-popup-request') {
                    msg = null; // Silencioso
                }

                if (msg) alert(msg);
                googleLoginBtn.disabled = false;
                googleLoginBtnText.textContent = originalText;
            }
        });
    </script>
@endsection
