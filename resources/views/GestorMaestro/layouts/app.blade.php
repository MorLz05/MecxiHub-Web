<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Gestor - MecxiHub')</title>

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
                class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4">
                    <button type="button" class="text-gray-500 hover:text-brand-blue lg:hidden">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
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
@stack('scripts')
</body>

</html>
