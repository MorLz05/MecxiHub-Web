@extends('conductor.layouts.app')

@section('title', 'Demasiados intentos - MecxiHub')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full text-center">

            <!-- Ícono de bloqueo animado -->
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fa-solid fa-shield-halved text-5xl text-red-500"></i>
            </div>

            <!-- Título -->
            <h2 class="text-3xl font-black mb-2">
                <span class="text-[#0039A6]">Acceso</span> <span class="text-[#FF6B00]">Bloqueado</span>
            </h2>

            <p class="text-gray-500 text-sm mb-8 leading-relaxed">
                Hemos detectado demasiados intentos de inicio de sesión fallidos desde tu red. Por tu seguridad, hemos pausado temporalmente el acceso.
            </p>

            <!-- Tarjeta del Reloj -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 mb-8 shadow-sm">
                <p class="text-sm font-semibold text-gray-600 mb-2">Tiempo restante de bloqueo:</p>

                <!-- Reloj -->
                <div class="flex items-center justify-center gap-2">
                    <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 shadow-sm">
                        <span id="timer" class="text-4xl font-black text-[#0039A6]">60</span>
                    </div>
                    <span class="text-lg font-bold text-gray-400">segundos</span>
                </div>
            </div>

            <!-- Botón de regreso (deshabilitado hasta que pase el tiempo) -->
            <a href="{{ route('login') }}" id="btnVolver"
               class="inline-block w-full py-3.5 px-6 rounded-xl bg-gray-300 text-gray-500 font-semibold text-base transition cursor-not-allowed pointer-events-none">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver al Login
            </a>
            <p class="text-xs text-gray-400 mt-4">El botón se habilitará cuando termine el tiempo.</p>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tiempo de bloqueo en segundos (Laravel throttle:5,1 lo hace por 60 seg)
            let timeLeft = 60;
            const timerElement = document.getElementById('timer');
            const btnVolver = document.getElementById('btnVolver');

            const countdown = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;

                // Cambiar color a naranja cuando queden menos de 15 segundos
                if (timeLeft <= 15) {
                    timerElement.classList.remove('text-[#0039A6]');
                    timerElement.classList.add('text-[#FF6B00]');
                }

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerElement.textContent = "0";

                    // Habilitar el botón
                    btnVolver.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed', 'pointer-events-none');
                    btnVolver.classList.add('bg-[#0039A6]', 'text-white', 'hover:bg-[#002B80]', 'shadow-lg');
                }
            }, 1000);
        });
    </script>
@endsection
