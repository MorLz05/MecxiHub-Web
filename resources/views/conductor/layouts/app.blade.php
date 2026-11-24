<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token para formularios Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MecxiHub - Tu auto, en las mejores manos')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- FontAwesome para Íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    {{-- Para estilos específicos de la página si fuesen necesarios --}}
    @stack('styles')
</head>

<body class="bg-brand-lightbg text-gray-800 font-sans antialiased">

    <!-- Incluimos el Navbar parcial -->
    @include('conductor.layouts.partials.navbar')

    <!-- Contenido principal de la página -->
    <main>
        @yield('content')
    </main>

    {{-- Para scripts específicos de la página --}}
    @stack('scripts')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.getRegistrations().then(function (registrations) {
                    registrations.forEach(function (registration) {
                        registration.unregister();
                    });
                });
                caches.keys().then(function (cacheNames) {
                    cacheNames
                        .filter(function (cacheName) {
                            return cacheName.startsWith('offline-cache-');
                        })
                        .forEach(function (cacheName) {
                            caches.delete(cacheName);
                        });
                });
            });
        }
    </script>
</body>

</html>
