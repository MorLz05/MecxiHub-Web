@extends('conductor.layouts.cuenta')

@section('cuenta-content')

    {{-- Header con acciones --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#0039A6]/10 flex items-center justify-center">
                    <i class="fa-solid fa-bell text-[#0039A6] text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Notificaciones</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if ($noLeidas > 0)
                            Tienes <strong class="text-[#FF6B00]">{{ $noLeidas }}</strong>
                            {{ $noLeidas === 1 ? 'notificación sin leer' : 'notificaciones sin leer' }}
                        @else
                            Estás al día 🎉
                        @endif
                    </p>
                </div>
            </div>

            @if ($noLeidas > 0)
                <form action="{{ route('cuenta.notificaciones.marcar-todas') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0039A6] text-white text-sm font-semibold hover:bg-blue-800 transition">
                        <i class="fa-solid fa-check-double"></i>
                        Marcar todas como leídas
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Lista --}}
    @if (empty($notificaciones))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-regular fa-bell text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Aún no tienes notificaciones</h3>
            <p class="text-gray-500 text-sm mt-1">
                Aquí verás cuando tus servicios estén listos y cuando los talleres respondan tus reseñas.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($notificaciones as $notif)
                @php
                    $noLeida = empty($notif['leida']);
                    $colores = [
                        'orange' => ['bg-orange-100', 'text-[#FF6B00]', 'border-orange-200'],
                        'green' => ['bg-green-100', 'text-green-600', 'border-green-200'],
                        'blue' => ['bg-blue-100', 'text-[#0039A6]', 'border-blue-200'],
                        'red' => ['bg-red-100', 'text-red-600', 'border-red-200'],
                    ];
                    $color = $colores[$notif['color'] ?? 'blue'] ?? $colores['blue'];
                    $url = $notif['url'] ?? null;
                @endphp

                <div
                    class="relative bg-white rounded-2xl shadow-sm border {{ $noLeida ? 'border-blue-200 bg-blue-50/20' : 'border-gray-200' }} p-5 hover:shadow-md transition">
                    @if ($noLeida)
                        <span class="absolute top-5 right-5 w-2.5 h-2.5 rounded-full bg-[#FF6B00] shadow-sm"></span>
                    @endif

                    <div class="flex items-start gap-4">
                        {{-- Icono --}}
                        <div
                            class="w-12 h-12 rounded-2xl {{ $color[0] }} flex items-center justify-center flex-shrink-0">
                            <i class="{{ $notif['icono'] ?? 'fa-solid fa-bell' }} {{ $color[1] }} text-lg"></i>
                        </div>

                        {{-- Contenido --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm {{ $noLeida ? '' : 'text-gray-700' }}">
                                        {{ $notif['titulo'] ?? 'Notificación' }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $notif['fecha_humana'] ?? 'Reciente' }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                                {{ $notif['mensaje'] ?? '' }}
                            </p>

                            {{-- Acciones --}}
                            <div class="flex items-center gap-3 mt-4 flex-wrap">

                                {{-- Botón principal según tipo --}}
                                @if (!empty($url))
                                    <a href="{{ $url }}" data-notif-id="{{ $notif['id'] }}"
                                        onclick="marcarLeidaYRedirigir(event, this)"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#FF6B00] text-white text-xs font-semibold hover:bg-orange-600 transition shadow-sm">
                                        @if (($notif['tipo'] ?? '') === 'servicio_listo')
                                            <i class="fa-solid fa-star"></i>
                                            Calificar mi experiencia
                                        @elseif (($notif['tipo'] ?? '') === 'respuesta_taller')
                                            <i class="fa-solid fa-comment-dots"></i>
                                            Ver respuesta
                                        @else
                                            <i class="fa-solid fa-arrow-right"></i>
                                            Ver más
                                        @endif
                                    </a>
                                @endif

                                {{-- Marcar leída --}}
                                @if ($noLeida)
                                    <form action="{{ route('cuenta.notificaciones.leida', ['id' => $notif['id']]) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            Marcar leída
                                        </button>
                                    </form>
                                @endif

                                {{-- Eliminar --}}
                                <form action="{{ route('cuenta.notificaciones.eliminar', ['id' => $notif['id']]) }}"
                                    method="POST" onsubmit="return confirm('¿Eliminar esta notificación?');"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-red-500 text-xs font-semibold hover:bg-red-50 transition">
                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Script para marcar como leída antes de redirigir --}}
    @push('scripts')
        <script>
            function marcarLeidaYRedirigir(event, el) {
                event.preventDefault();
                const url = el.href;
                const notifId = el.dataset.notifId;

                if (!notifId) {
                    window.location.href = url;
                    return;
                }

                fetch(`{{ url('cuenta/notificaciones') }}/${notifId}/leida`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({})
                    })
                    .finally(() => {
                        window.location.href = url;
                    });
            }
        </script>
    @endpush

@endsection
