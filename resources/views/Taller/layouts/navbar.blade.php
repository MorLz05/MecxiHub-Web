<!-- navbar.blade.php -->
<aside id="sidebar"
    class="w-64 bg-gradient-to-b from-brand-darkblue via-brand-darkblue to-[#001c59] text-white flex flex-col justify-between flex-shrink-0 z-50 border-r border-white/5 shadow-2xl sidebar-transition
           fixed top-0 left-0 h-full -translate-x-full
           lg:static lg:translate-x-0 lg:h-full">

    <div class="flex flex-col flex-1 min-h-0">
        <!-- Logo Area -->
        <div class="p-5 flex-shrink-0">
            <a href="{{ route('taller.dashboard') }}"
                class="relative block bg-gradient-to-b from-white/10 to-white/5 backdrop-blur-md rounded-2xl p-4 border border-white/15 shadow-xl shadow-black/20 group transition duration-300 hover:border-white/25">
                <div
                    class="absolute inset-0 bg-brand-blue/20 rounded-2xl filter blur-md opacity-0 group-hover:opacity-100 transition duration-300">
                </div>
                <div class="flex justify-center items-center relative z-10">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="MecxiHub Logo"
                        class="h-10 w-auto object-contain drop-shadow-md">
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="mt-2 px-4 space-y-2 overflow-y-auto sidebar-scroll flex-1 min-h-0">
            <!-- Resumen -->
            <a href="{{ route('taller.dashboard') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.dashboard') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.dashboard'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.dashboard') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-chart-simple text-base"></i>
                </div>
                <span class="tracking-wide">Resumen</span>
            </a>

            <!-- Información -->
            <a href="{{ route('taller.informacion') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.informacion*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.informacion*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.informacion*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <span class="tracking-wide">Información</span>
            </a>

            <!-- Órdenes con badge -->
            <a href="{{ route('taller.ordenes') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.ordenes*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.ordenes*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.ordenes*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-clipboard-list text-base"></i>
                </div>
                <span class="tracking-wide">Órdenes</span>
            </a>

            <!-- Personal -->
            <a href="{{ route('taller.personal') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.personal*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.personal*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.personal*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-users text-base"></i>
                </div>
                <span class="tracking-wide">Personal</span>
            </a>

            <!-- Asistente IA -->
            <a href="{{ route('taller.asistente') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.asistente*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.asistente*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.asistente*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-robot text-base"></i>
                </div>
                <span class="tracking-wide">Asistente IA</span>
            </a>

            <!-- Comentarios -->
            <a href="{{ route('taller.comentarios') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.comentarios*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.comentarios*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.comentarios*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-star text-base"></i>
                </div>
                <span class="tracking-wide">Comentarios</span>
                {{-- <span
                    class="ml-auto bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">4.8</span> --}}
            </a>

            <!-- Separador -->
            <div class="pt-3 pb-1">
                <div class="border-t border-white/10"></div>
            </div>

            <!-- Planes -->
            <a href="{{ route('taller.planes') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.planes*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.planes*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.planes*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-crown text-base"></i>
                </div>
                <span class="tracking-wide">Planes</span>
            </a>

            <!-- Cuenta / Seguridad -->
            <a href="{{ route('taller.seguridad') }}"
                class="relative flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group {{ request()->routeIs('taller.seguridad*') ? 'bg-gradient-to-r from-brand-blue to-blue-600 text-white shadow-lg shadow-blue-500/30 border-t border-white/25 translate-x-1' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">

                @if (request()->routeIs('taller.seguridad*'))
                    <span class="absolute -left-1 top-2 bottom-2 w-1.5 bg-brand-orange rounded-r-full shadow-sm"></span>
                @endif

                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 {{ request()->routeIs('taller.seguridad*') ? 'bg-white/20 text-white' : 'bg-white/5 text-blue-200 group-hover:bg-white/10' }}">
                    <i class="fa-solid fa-shield-halved text-base"></i>
                </div>
                <span class="tracking-wide">Cuenta</span>
            </a>
        </nav>
    </div>

    <!-- Cerrar Sesión Footer -->
    <div class="p-4 border-t border-white/10 bg-black/10 flex-shrink-0">
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

<!-- Mobile Toggle Button (visible solo en móvil) -->
<button id="sidebar-toggle"
    class="lg:hidden fixed top-4 left-4 z-[60] bg-brand-darkblue text-white p-3 rounded-xl shadow-lg transition-all duration-200">
    <i class="fa-solid fa-bars text-xl"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const mq = window.matchMedia('(min-width: 1024px)');

        function isDesktop() {
            return mq.matches;
        }

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function syncWithViewport() {
            if (isDesktop()) {
                // En desktop: siempre visible, sin overlay, sin scroll bloqueado
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            } else {
                // En móvil: oculta por defecto
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        syncWithViewport();

        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !isDesktop() && !sidebar.classList.contains(
                '-translate-x-full')) {
                closeSidebar();
            }
        });

        // Listener moderno para cambios de viewport
        mq.addEventListener('change', syncWithViewport);
    });
</script>
