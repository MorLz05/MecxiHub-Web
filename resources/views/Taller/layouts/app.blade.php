<!-- app.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Taller - MecxiHub')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-orange': '#FF8800',
                        'brand-blue': '#0066FF',
                        'brand-darkblue': '#001B5E',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        /* Sidebar transition */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* Scrollbar for sidebar */
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
    </style>

    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Izquierdo / Navbar -->
        @include('taller.layouts.navbar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-50">
            <main class="p-6 md:p-8 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Overlay para móvil -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <!-- Scripts base -->
    @stack('scripts')
</body>

</html>
