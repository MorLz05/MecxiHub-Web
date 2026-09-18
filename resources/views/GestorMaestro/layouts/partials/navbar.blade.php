<aside
    class="w-64 bg-gradient-to-b from-brand-darkblue via-brand-darkblue to-[#001c59] text-white flex flex-col justify-between hidden lg:flex flex-shrink-0 z-40 border-r border-white/5 shadow-2xl">
    <div>
        <!-- Logo Area -->
        <div class="p-5">
            <div
                class="relative bg-gradient-to-b from-white/10 to-white/5 backdrop-blur-md rounded-2xl p-4 border border-white/15 shadow-xl shadow-black/20 flex justify-center items-center group transition duration-300 hover:border-white/25">
                <div
                    class="absolute inset-0 bg-brand-blue/20 rounded-2xl filter blur-md opacity-0 group-hover:opacity-100 transition duration-300">
                </div>
                <img src="{{ asset('images/logo/logo.png') }}" alt="MecxiHub Logo"
                    class="h-10 w-auto object-contain relative z-10 drop-shadow-md">
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="mt-2 px-4 space-y-2">
            <!-- Cuenta -->
            <a href="{{ route('gestor.cuenta') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('gestor.cuenta') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('gestor.cuenta'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('gestor.cuenta') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-user text-base"></i>
                </div>
                <span class="tracking-wide">Mi Cuenta</span>
            </a>

            <!-- Talleres -->
            <a href="{{ route('gestor.talleres') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('gestor.talleres') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('gestor.talleres'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('gestor.talleres') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-warehouse text-base"></i>
                </div>
                <span class="tracking-wide">Talleres</span>
            </a>

            <!-- Usuarios -->
            <a href="{{ route('gestor.usuarios') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('gestor.usuarios*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('gestor.usuarios*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('gestor.usuarios*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-users text-base"></i>
                </div>
                <span class="tracking-wide">Usuarios</span>
            </a>

            <!-- Planes -->
            <a href="{{ route('gestor.planes') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('gestor.planes*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('gestor.planes*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('gestor.planes*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-crown text-base"></i>
                </div>
                <span class="tracking-wide">Planes</span>
            </a>

            <!-- Separador -->
            <div class="pt-3 pb-1">
                <div class="border-t border-white/10"></div>
            </div>

            <!-- Volver al sitio -->
            <a href="{{ route('home') }}"
                class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-blue-200/60 hover:bg-white/5 hover:text-white transition-all duration-200 group">
                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/5 text-blue-300 group-hover:bg-white/10 transition-transform group-hover:-translate-x-1">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </div>
                <span>Volver al sitio</span>
            </a>
        </nav>
    </div>

    <!-- Cerrar Sesión Footer -->
    <div class="p-4 border-t border-white/10 bg-black/10">
        <form action="{{ route('logout') }}" method="POST" class="block">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl text-xs font-bold text-red-300 hover:text-white bg-red-500/10 hover:bg-gradient-to-r hover:from-red-600 hover:to-red-500 transition-all duration-200 border border-red-500/20 shadow-md hover:shadow-red-900/40 group">
                <i class="fa-solid fa-right-from-bracket text-sm transition-transform group-hover:-translate-x-0.5"></i>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>
