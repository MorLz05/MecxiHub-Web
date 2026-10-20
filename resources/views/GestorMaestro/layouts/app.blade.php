<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Gestor - MecxiHub')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0039A6',
                            darkblue: '#002677',
                            orange: '#FF6B00',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Sidebar scrollbar fino */
        .sidebar-scroll::-webkit-scrollbar {
            width: 3px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Sidebar transition (móvil) */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
    </style>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Izquierdo / Navbar -->
        @include('GestorMaestro.layouts.partials.navbar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-50">

            <!-- Header Superior -->
            <header
                class="bg-white border-b border-gray-200 px-6 py-4 pl-16 lg:pl-6 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">@yield('header-title', 'Panel de Gestor')</h1>
                        <p class="text-xs text-gray-500">@yield('header-subtitle', 'Gestiona el sistema desde aquí')</p>
                    </div>
                </div>

                <!-- User Badge -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 hidden sm:block">
                        {{ session('firebase_user.nombre_completo', 'Gestor') }}
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6 space-y-6">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- Overlay para móvil -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    @stack('scripts')

    <!-- Script del sidebar móvil -->
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
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
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

            mq.addEventListener('change', syncWithViewport);
        });
    </script>
</body>

</html>
