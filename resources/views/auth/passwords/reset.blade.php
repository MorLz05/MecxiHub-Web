@extends('conductor.layouts.app')

@section('title', 'Restablecer contraseña - MecxiHub')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">

            <!-- Título -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black">
                    <span class="text-[#0039A6]">Mecxi</span><span class="text-[#FF6B00]">Hub</span>
                </h2>
                <p class="text-gray-500 text-sm mt-2">Ingresa tu nueva contraseña</p>
            </div>

            <!-- Mensajes de error/success -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-xl mt-0.5"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Formulario -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva
                            contraseña</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation"
                            class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar contraseña</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                placeholder="Confirma tu nueva contraseña"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-[#0039A6] text-gray-800 placeholder-gray-400 text-base bg-gray-50/50 shadow-inner transition">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-6 rounded-xl bg-[#FF6B00] hover:bg-orange-600 text-white font-semibold text-base shadow-lg shadow-orange-500/30 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span>Actualizar contraseña</span>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-[#0039A6] hover:underline">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
