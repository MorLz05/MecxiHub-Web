<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="w-full px-4 sm:px-6 lg:px-8 h-24 flex items-center justify-between">
        <!-- Logo con enlace a inicio -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0 hover:opacity-80 transition">
            <img src="{{ asset('images/logo/logo.png') }}" alt="MecxiHub Logo" class="h-12 sm:h-14 md:h-16 lg:h-20 w-auto">
        </a>

        <!-- Navigation Links - Desktop -->
        <nav class="hidden lg:flex items-center gap-1 bg-gray-50 p-1 rounded-full border border-gray-100">
            <!-- Inicio -->
            <a href="{{ route('home') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-full font-medium shadow-sm transition-all duration-200
                {{ request()->routeIs('home')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-house"></i>
                Inicio
            </a>

            <!-- Buscar talleres -->
            <a href="{{ route('buscar.talleres') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-full font-medium transition-all duration-200
                {{ request()->routeIs('buscar.talleres')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-magnifying-glass"></i>
                Buscar talleres
            </a>

            <!-- Mis servicios -->
            {{-- <a href="{{ route('mis.servicios') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-full font-medium transition-all duration-200
                {{ request()->routeIs('mis.servicios')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-regular fa-calendar-check"></i>
                Mis servicios
            </a> --}}

            <!-- Asistente IA -->
            <a href="{{ route('asistente.ia') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-full font-medium transition-all duration-200
                {{ request()->routeIs('asistente.ia')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-robot"></i>
                Asistente IA
            </a>

            <!-- Si el usuario está autenticado (verificando sesión) -->
            @if (session()->has('firebase_user'))
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 px-3 py-2 rounded-full font-medium transition-all duration-200
                        text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]">
                        <i class="fa-regular fa-user-circle text-lg"></i>
                        <span class="max-w-[100px] truncate">
                            {{ session('firebase_user.nombre_completo', 'Mi cuenta') }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                        style="display: none;" x-show="open">

                        <!-- Información del usuario -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ session('firebase_user.nombre_completo') }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ session('firebase_user.email') }}
                            </p>
                            <span class="inline-block mt-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                {{ session('firebase_user.rol', 'Conductor') }}
                            </span>
                        </div>

                        @php
                            $userRole = session('firebase_user.rol', 'Conductor');

                            if ($userRole === 'GestorMaestro') {
                                $accountRoute = route('gestor.cuenta');
                                $accountLabel = 'Panel Gestor';
                                $accountIcon = 'fa-solid fa-user-tie';
                            } elseif ($userRole === 'Administrador') {
                                $accountRoute = route('taller.dashboard');
                                $accountLabel = 'Mi Taller';
                                $accountIcon = 'fa-solid fa-warehouse';
                            } else {
                                $accountRoute = route('cuenta.resumen');
                                $accountLabel = 'Mi cuenta';
                                $accountIcon = 'fa-regular fa-id-card';
                            }
                        @endphp

                        <a href="{{ $accountRoute }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <i class="{{ $accountIcon }} text-gray-400"></i>
                            {{ $accountLabel }}
                        </a>

                        <hr class="my-1 border-gray-100">

                        <!-- Cerrar sesión -->
                        <form action="{{ route('logout') }}" method="POST" class="block">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition w-full">
                                <i class="fa-solid fa-right-from-bracket text-red-400"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Login (solo cuando no está autenticado) -->
                <a href="{{ route('login') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-full font-medium transition-all duration-200
                    {{ request()->routeIs('login')
                        ? 'bg-brand-blue text-white'
                        : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                    <i class="fa-regular fa-user-circle"></i>
                    Login
                </a>
            @endif
        </nav>

        <!-- Mobile Menu Button -->
        <button id="mobile-menu-button"
            class="lg:hidden flex items-center text-gray-700 hover:text-brand-blue transition p-2">
            <i class="fa-solid fa-bars text-2xl"></i>
        </button>
    </div>

    <!-- Mobile Menu - Dropdown -->
    <div id="mobile-menu" class="lg:hidden hidden bg-white border-t border-gray-100 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3 space-y-2">
            <!-- Inicio -->
            <a href="{{ route('home') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 w-full
                {{ request()->routeIs('home')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-house"></i>
                Inicio
            </a>

            <!-- Buscar talleres -->
            <a href="{{ route('buscar.talleres') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 w-full
                {{ request()->routeIs('buscar.talleres')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-magnifying-glass"></i>
                Buscar talleres
            </a>

            <!-- Mis servicios -->
            {{-- <a href="{{ route('mis.servicios') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 w-full
                {{ request()->routeIs('mis.servicios')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-regular fa-calendar-check"></i>
                Mis servicios
            </a> --}}

            <!-- Asistente IA -->
            <a href="{{ route('asistente.ia') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 w-full
                {{ request()->routeIs('asistente.ia')
                    ? 'bg-brand-blue text-white'
                    : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                <i class="fa-solid fa-robot"></i>
                Asistente IA
            </a>

            <!-- Si el usuario está autenticado (verificando sesión) -->
            @if (session()->has('firebase_user'))
                <div class="border-t border-gray-100 pt-2 mt-2">
                    <div class="px-4 py-2">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ session('firebase_user.nombre_completo') }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ session('firebase_user.email') }}
                        </p>
                        <span class="inline-block mt-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                            {{ session('firebase_user.rol', 'Conductor') }}
                        </span>
                    </div>

                    @php
                        $userRole = session('firebase_user.rol', 'Conductor');

                        if ($userRole === 'GestorMaestro') {
                            $accountRoute = route('gestor.cuenta');
                            $accountLabel = 'Panel Gestor';
                            $accountIcon = 'fa-solid fa-user-tie';
                        } elseif ($userRole === 'Administrador') {
                            $accountRoute = route('taller.dashboard');
                            $accountLabel = 'Mi Taller';
                            $accountIcon = 'fa-solid fa-warehouse';
                        } else {
                            $accountRoute = route('cuenta.resumen');
                            $accountLabel = 'Mi cuenta';
                            $accountIcon = 'fa-regular fa-id-card';
                        }
                    @endphp

                    <a href="{{ $accountRoute }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <i class="{{ $accountIcon }} text-gray-400"></i>
                        {{ $accountLabel }}
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition w-full">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @else
                <!-- Login (solo cuando no está autenticado) -->
                <a href="{{ route('login') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 w-full
                    {{ request()->routeIs('login')
                        ? 'bg-brand-blue text-white'
                        : 'text-gray-600 hover:text-brand-blue hover:drop-shadow-[0_0_6px_rgba(13,76,211,0.55)]' }}">
                    <i class="fa-regular fa-user-circle"></i>
                    Login
                </a>
            @endif
        </div>
    </div>
</header>

<!-- Script para el menú móvil -->
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });

                // Cerrar menú al hacer clic fuera
                document.addEventListener('click', function(event) {
                    const isClickInside = mobileMenuButton.contains(event.target) || mobileMenu.contains(event.target);
                    if (!isClickInside && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endpush
