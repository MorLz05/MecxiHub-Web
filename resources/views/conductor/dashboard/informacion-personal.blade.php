@extends('conductor.layouts.cuenta')

@section('title', 'Información Personal - MecxiHub')

@section('cuenta-content')
    <!-- Mensajes de éxito/error -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center gap-3">
            <i class="fa-solid fa-circle-xmark text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- === FORMULARIO DE INFORMACIÓN PERSONAL === -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 pt-4">
            <h3 class="text-lg font-bold text-gray-900 pb-3">Información personal</h3>
        </div>

        <div class="p-6">
            <!-- Formulario de Perfil -->
            <form action="{{ route('usuario.actualizar.perfil') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="nombre_completo" class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre completo</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-gray-400">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" id="nombre_completo" name="nombre_completo"
                            value="{{ old('nombre_completo', session('firebase_user.nombre_completo')) }}"
                            required
                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo electrónico</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email"
                            value="{{ old('email', session('firebase_user.email')) }}" required
                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Cambiar tu correo requerirá iniciar sesión nuevamente.</p>
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#0039A6] hover:bg-[#002B80] text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-blue-900/20">
                    <i class="fa-solid fa-save"></i>
                    Actualizar perfil
                </button>
            </form>

            <!-- Separador -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Formulario de Cambio de Contraseña -->
            <h4 class="font-semibold text-gray-900 mb-4 text-lg">Cambiar contraseña</h4>
            <form action="{{ route('usuario.actualizar.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="password_actual" class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña actual</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password_actual" name="password_actual"
                            placeholder="Ingresa tu contraseña actual" required
                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        <button type="button" class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_nueva" class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva contraseña</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-gray-400">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" id="password_nueva" name="password_nueva"
                            placeholder="Crea una nueva contraseña" minlength="6" required
                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        <button type="button" class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_nueva_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar nueva contraseña</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-gray-400">
                            <i class="fa-solid fa-shield-check"></i>
                        </span>
                        <input type="password" id="password_nueva_confirmation" name="password_nueva_confirmation"
                            placeholder="Confirma tu nueva contraseña" required
                            class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100 text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 transition">
                        <button type="button" class="toggle-password absolute right-4 text-gray-400 hover:text-gray-600 transition">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div id="passwordMatchIndicatorPersonal" class="hidden text-sm font-medium">
                    <span id="matchTextPersonal" class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-green-500"></i>
                        Las contraseñas coinciden
                    </span>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Al cambiar tu contraseña, serás redirigido al inicio de sesión para continuar con tu nueva contraseña.
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#FF6B00] hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-key"></i>
                    Cambiar contraseña
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Toggle password visibility ===
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

            // === Password match validation ===
            const passwordNueva = document.getElementById('password_nueva');
            const passwordConfirm = document.getElementById('password_nueva_confirmation');
            const matchIndicator = document.getElementById('passwordMatchIndicatorPersonal');
            const matchText = document.getElementById('matchTextPersonal');

            function validatePasswordMatch() {
                const pwd = passwordNueva.value;
                const confirm = passwordConfirm.value;

                if (confirm.length > 0) {
                    matchIndicator.classList.remove('hidden');
                    if (pwd === confirm && pwd.length > 0) {
                        matchText.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i> Las contraseñas coinciden';
                        matchText.className = 'flex items-center gap-2 text-green-600';
                        passwordConfirm.classList.remove('border-red-500');
                        passwordConfirm.classList.add('border-green-500');
                    } else {
                        matchText.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500"></i> Las contraseñas no coinciden';
                        matchText.className = 'flex items-center gap-2 text-red-600';
                        passwordConfirm.classList.remove('border-green-500');
                        passwordConfirm.classList.add('border-red-500');
                    }
                } else {
                    matchIndicator.classList.add('hidden');
                    passwordConfirm.classList.remove('border-red-500', 'border-green-500');
                }
            }

            if (passwordNueva && passwordConfirm) {
                passwordNueva.addEventListener('input', validatePasswordMatch);
                passwordConfirm.addEventListener('input', validatePasswordMatch);
            }
        });
    </script>
@endsection
