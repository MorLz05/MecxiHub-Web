@extends('taller.layouts.app')

@section('title', 'Seguridad - Panel Taller')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Título de página -->
            <div class="mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#0066FF]/10 to-[#001B5E]/10 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-2xl text-[#0066FF]"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Seguridad</h1>
                        <p class="text-gray-500 text-sm mt-1">Gestiona tu correo electrónico y contraseña.</p>
                    </div>
                </div>
            </div>

            <!-- Mensajes de éxito/error -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 flex items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 flex items-center gap-3 shadow-sm">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- === COLUMNA IZQUIERDA: FORMULARIOS === -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- === CAMBIAR CORREO === -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="border-b border-gray-200/50 px-6 py-4 bg-gradient-to-r from-blue-50/50 to-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#0066FF] to-[#001B5E] flex items-center justify-center shadow-sm">
                                    <i class="fa-solid fa-envelope text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Cambiar correo electrónico</h3>
                                    <p class="text-sm text-gray-500">Actualiza la dirección de correo de tu cuenta.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <form action="{{ route('taller.seguridad.update.email') }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Nuevo correo electrónico <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-envelope"></i>
                                        </span>
                                        <input type="email" id="email" name="email"
                                            value="{{ old('email', $user['email']) }}" required
                                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-info-circle text-[#0066FF]"></i>
                                        Al cambiar tu correo, serás redirigido al inicio de sesión.
                                    </p>
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#0066FF] to-[#001B5E] hover:from-[#0055DD] hover:to-[#001B5E] text-white rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-900/20 hover:shadow-blue-900/30 hover:scale-[1.02]">
                                    <i class="fa-solid fa-save"></i>
                                    Actualizar correo
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- === CAMBIAR CONTRASEÑA === -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="border-b border-gray-200/50 px-6 py-4 bg-gradient-to-r from-orange-50/50 to-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#FF8800] to-orange-500 flex items-center justify-center shadow-sm">
                                    <i class="fa-solid fa-key text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Cambiar contraseña</h3>
                                    <p class="text-sm text-gray-500">Actualiza la contraseña de tu cuenta.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <form action="{{ route('taller.seguridad.update.password') }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="password_actual" class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña actual <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                        <input type="password" id="password_actual" name="password_actual"
                                            placeholder="Ingresa tu contraseña actual" required
                                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                        <button type="button"
                                            class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="password_nueva" class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva contraseña <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-key"></i>
                                        </span>
                                        <input type="password" id="password_nueva" name="password_nueva"
                                            placeholder="Crea una nueva contraseña (mínimo 6 caracteres)" minlength="6"
                                            required
                                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                        <button type="button"
                                            class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div id="passwordStrengthBar" class="h-full w-0 transition-all duration-500 rounded-full"></div>
                                        </div>
                                        <span id="passwordStrengthText" class="text-xs font-medium text-gray-500 min-w-[60px]">Débil</span>
                                    </div>
                                </div>

                                <div>
                                    <label for="password_nueva_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar nueva contraseña <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-4 text-gray-400">
                                            <i class="fa-solid fa-shield-check"></i>
                                        </span>
                                        <input type="password" id="password_nueva_confirmation"
                                            name="password_nueva_confirmation" placeholder="Confirma tu nueva contraseña"
                                            required
                                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:border-[#0066FF] focus:ring-4 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition duration-200">
                                        <button type="button"
                                            class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatchIndicator" class="hidden mt-1.5 text-sm font-medium">
                                        <span id="matchText" class="flex items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                            Las contraseñas coinciden
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-blue-50/80 border border-blue-200/60 rounded-xl p-4 text-sm text-blue-800 flex items-start gap-2">
                                    <i class="fa-solid fa-info-circle text-[#0066FF] mt-0.5"></i>
                                    <span>Al cambiar tu contraseña, serás redirigido al inicio de sesión para continuar con tu nueva contraseña.</span>
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#FF8800] to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-orange-500/30 hover:shadow-orange-500/40 hover:scale-[1.02]">
                                    <i class="fa-solid fa-key"></i>
                                    Cambiar contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- === COLUMNA DERECHA: CONSEJOS DE SEGURIDAD === -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="bg-gradient-to-br from-[#001B5E] to-[#0066FF] rounded-2xl shadow-xl shadow-blue-900/20 overflow-hidden border border-blue-400/20">
                            <div class="px-5 py-4 border-b border-white/10">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-shield-check text-[#FF8800] text-xl"></i>
                                    <h3 class="text-lg font-bold text-white">Consejos de seguridad</h3>
                                </div>
                                <p class="text-xs text-blue-300/80 mt-0.5">Recomendaciones para proteger tu cuenta</p>
                            </div>

                            <div class="p-5 space-y-3">
                                <!-- Consejo 1 -->
                                <div class="flex items-start gap-3 p-3.5 bg-white/5 rounded-xl border border-white/10 hover:bg-white/10 transition duration-200 group">
                                    <div class="w-8 h-8 rounded-lg bg-[#FF8800]/20 flex items-center justify-center flex-shrink-0 group-hover:bg-[#FF8800]/30 transition">
                                        <i class="fa-solid fa-key text-[#FF8800] text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Contraseña segura</p>
                                        <p class="text-xs text-blue-200/80 mt-0.5">Usa al menos 8 caracteres con mayúsculas, minúsculas y números.</p>
                                    </div>
                                </div>

                                <!-- Consejo 2 -->
                                <div class="flex items-start gap-3 p-3.5 bg-white/5 rounded-xl border border-white/10 hover:bg-white/10 transition duration-200 group">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500/30 transition">
                                        <i class="fa-solid fa-ban text-emerald-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">No reutilices contraseñas</p>
                                        <p class="text-xs text-blue-200/80 mt-0.5">Usa una contraseña única para tu cuenta de MecxiHub.</p>
                                    </div>
                                </div>

                                <!-- Consejo 3 -->
                                <div class="flex items-start gap-3 p-3.5 bg-white/5 rounded-xl border border-white/10 hover:bg-white/10 transition duration-200 group">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-500/30 transition">
                                        <i class="fa-solid fa-envelope text-blue-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Mantén tu correo actualizado</p>
                                        <p class="text-xs text-blue-200/80 mt-0.5">Asegúrate de tener acceso al correo registrado.</p>
                                    </div>
                                </div>

                                <!-- Consejo 4 -->
                                <div class="flex items-start gap-3 p-3.5 bg-white/5 rounded-xl border border-white/10 hover:bg-white/10 transition duration-200 group">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-500/30 transition">
                                        <i class="fa-solid fa-right-from-bracket text-purple-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Cierra sesión en dispositivos públicos</p>
                                        <p class="text-xs text-blue-200/80 mt-0.5">Siempre cierra sesión al usar computadoras compartidas.</p>
                                    </div>
                                </div>

                                <!-- Consejo 5 - Extra -->
                                <div class="flex items-start gap-3 p-3.5 bg-white/5 rounded-xl border border-white/10 hover:bg-white/10 transition duration-200 group">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-500/30 transition">
                                        <i class="fa-solid fa-shield-halved text-rose-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Autenticación en dos pasos</p>
                                        <p class="text-xs text-blue-200/80 mt-0.5">Próximamente: Añade una capa extra de seguridad a tu cuenta.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer del consejo -->
                            <div class="px-5 py-3 border-t border-white/10 bg-white/5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-blue-300/60">🔒 Tu seguridad es importante</span>
                                    <i class="fa-solid fa-lock text-blue-400/40 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.closest('.relative').querySelector('input');
                    const icon = this.querySelector('i');
                    if (input.getAttribute('type') === 'password') {
                        input.setAttribute('type', 'text');
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.setAttribute('type', 'password');
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Password match validation
            const passwordNueva = document.getElementById('password_nueva');
            const passwordConfirm = document.getElementById('password_nueva_confirmation');
            const matchIndicator = document.getElementById('passwordMatchIndicator');
            const matchText = document.getElementById('matchText');

            if (passwordNueva && passwordConfirm) {
                function validatePasswordMatch() {
                    const pwd = passwordNueva.value;
                    const confirm = passwordConfirm.value;

                    if (confirm.length > 0) {
                        matchIndicator.classList.remove('hidden');
                        if (pwd === confirm && pwd.length > 0) {
                            matchText.innerHTML =
                                '<i class="fa-solid fa-circle-check text-emerald-500"></i> Las contraseñas coinciden';
                            matchText.className = 'flex items-center gap-2 text-emerald-600';
                            passwordConfirm.classList.remove('border-red-500');
                            passwordConfirm.classList.add('border-emerald-500');
                        } else {
                            matchText.innerHTML =
                                '<i class="fa-solid fa-circle-xmark text-red-500"></i> Las contraseñas no coinciden';
                            matchText.className = 'flex items-center gap-2 text-red-600';
                            passwordConfirm.classList.remove('border-emerald-500');
                            passwordConfirm.classList.add('border-red-500');
                        }
                    } else {
                        matchIndicator.classList.add('hidden');
                        passwordConfirm.classList.remove('border-red-500', 'border-emerald-500');
                    }
                }

                passwordNueva.addEventListener('input', validatePasswordMatch);
                passwordConfirm.addEventListener('input', validatePasswordMatch);
            }

            // Password strength indicator
            const passwordInput = document.getElementById('password_nueva');
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');

            if (passwordInput && strengthBar && strengthText) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    let label = 'Débil';
                    let color = '#ef4444';

                    if (password.length >= 6) strength++;
                    if (password.length >= 10) strength++;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                    if (/\d/.test(password)) strength++;
                    if (/[^a-zA-Z0-9]/.test(password)) strength++;

                    const percentage = Math.min((strength / 5) * 100, 100);

                    if (strength <= 2) {
                        label = 'Débil';
                        color = '#ef4444';
                    } else if (strength === 3) {
                        label = 'Media';
                        color = '#f59e0b';
                    } else if (strength === 4) {
                        label = 'Fuerte';
                        color = '#22c55e';
                    } else if (strength === 5) {
                        label = 'Muy fuerte';
                        color = '#10b981';
                    }

                    strengthBar.style.width = percentage + '%';
                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = label;
                    strengthText.style.color = color;
                });
            }
        });
    </script>
@endsection
