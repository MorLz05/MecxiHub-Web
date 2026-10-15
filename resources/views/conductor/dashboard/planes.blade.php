@extends('conductor.layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- Título de página --}}
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Planes disponibles</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Elige el plan que mejor se adapte a ti. Todos incluyen acceso a la plataforma MecxiHub.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- === SIDEBAR - Tabs === --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0039A6]/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-user text-[#0039A6] text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ session('firebase_user.nombre_completo') }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ session('firebase_user.email') }}</p>
                                </div>
                            </div>
                        </div>

                        <nav class="p-2 space-y-1">
                            <a href="{{ route('cuenta.resumen') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.resumen') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-chart-simple text-base"></i>
                                Resumen de cuenta
                            </a>
                            <a href="{{ route('cuenta.informacion-personal') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.informacion-personal') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-user-pen text-base"></i>
                                Información personal
                            </a>
                            <a href="{{ route('cuenta.servicios.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.servicios*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-wrench text-base"></i>
                                Mis servicios
                            </a>
                            <a href="{{ route('cuenta.notificaciones.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.notificaciones*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-bell text-base"></i>
                                Notificaciones
                                @if (($notifNoLeidas ?? 0) > 0)
                                    <span class="ml-auto bg-[#FF6B00] text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $notifNoLeidas > 9 ? '9+' : $notifNoLeidas }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('cuenta.planes') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-medium text-sm
                                {{ request()->routeIs('cuenta.planes*') ? 'bg-[#0039A6] text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-[#0039A6]' }}">
                                <i class="fa-solid fa-crown text-base"></i>
                                Planes
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-red-600 hover:bg-red-50 font-medium text-sm transition w-full">
                                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>

                {{-- === CONTENIDO PRINCIPAL === --}}
                <div class="lg:col-span-3 space-y-6">

                    {{-- Mensajes flash --}}
                    @if (session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-green-500"></i>
                            <span class="text-sm">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            <span class="text-sm">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (empty($planes))
                        {{-- Estado vacío --}}
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12">
                            <div class="flex flex-col items-center text-center gap-3 max-w-md mx-auto">
                                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fa-solid fa-crown text-3xl text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Aún no hay planes disponibles</h3>
                                <p class="text-sm text-gray-500">
                                    En este momento no hay planes dirigidos a conductores. Vuelve a intentarlo más tarde o contacta con soporte.
                                </p>
                                <a href="{{ route('cuenta.resumen') }}"
                                    class="mt-2 px-5 py-2.5 bg-[#0039A6] hover:bg-[#002a7a] text-white rounded-xl font-semibold text-sm transition">
                                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver al resumen
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Grid de planes --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($planes as $plan)
                                @php
                                    $esActual = $plan['es_plan_actual'] ?? false;
                                    $destacado = !empty($plan['destacado']);
                                    $moneda = $plan['moneda'] ?? 'USD';
                                    $simbolos = ['USD' => '$', 'MXN' => '$', 'EUR' => '€'];
                                    $simbolo = $simbolos[$moneda] ?? '$';
                                    $caracteristicas = $plan['caracteristicas'] ?? [];
                                @endphp

                                <div class="plan-card relative bg-white rounded-2xl border-2 {{ $destacado ? 'destacado border-[#FF6B00]' : 'border-gray-200' }} shadow-sm overflow-hidden flex flex-col">

                                    {{-- Ribbon si es destacado --}}
                                    @if ($destacado)
                                        <div class="absolute top-0 right-0 bg-gradient-to-r from-[#FF6B00] to-orange-500 text-white text-[10px] font-bold uppercase tracking-wider px-4 py-1.5 rounded-bl-xl shadow-md">
                                            <i class="fa-solid fa-star text-[8px] mr-1"></i> Recomendado
                                        </div>
                                    @endif

                                    {{-- Cabecera --}}
                                    <div class="p-6 pb-4 border-b border-gray-100">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-11 h-11 rounded-xl {{ $destacado ? 'bg-[#FF6B00]/10 text-[#FF6B00]' : 'bg-[#0039A6]/10 text-[#0039A6]' }} flex items-center justify-center">
                                                <i class="fa-solid fa-box-open text-lg"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-bold text-gray-900 truncate">
                                                    {{ $plan['nombre'] ?? 'Plan sin nombre' }}
                                                </h3>
                                                <p class="text-xs text-gray-500">
                                                    {{ $plan['duracion_dias'] ?? 0 }} días de acceso
                                                </p>
                                            </div>
                                        </div>

                                        @if (!empty($plan['descripcion']))
                                            <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                                {{ $plan['descripcion'] }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Precio --}}
                                    <div class="px-6 py-5 bg-gradient-to-b from-gray-50/70 to-white">
                                        <div class="flex items-end gap-2">
                                            <span class="text-4xl font-extrabold text-gray-900 leading-none">
                                                {{ $simbolo }}{{ number_format($plan['precio'] ?? 0, 2) }}
                                            </span>
                                            <span class="text-xs font-semibold text-gray-500 uppercase pb-1">
                                                {{ $moneda }} / {{ $plan['duracion_dias'] ?? 0 }}d
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Características --}}
                                    <div class="px-6 pb-6 flex-1">
                                        @if (!empty($caracteristicas) && is_array($caracteristicas))
                                            <ul class="space-y-2.5">
                                                @foreach ($caracteristicas as $car)
                                                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                                        <span class="flex-shrink-0 w-5 h-5 rounded-full {{ $destacado ? 'bg-[#FF6B00]/15 text-[#FF6B00]' : 'bg-green-100 text-green-600' }} flex items-center justify-center mt-0.5">
                                                            <i class="fa-solid fa-check text-[10px]"></i>
                                                        </span>
                                                        <span class="leading-relaxed">{{ $car }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-xs text-gray-400 italic">Sin características especificadas.</p>
                                        @endif
                                    </div>

                                    {{-- Footer con botón --}}
                                    <div class="px-6 pb-6 pt-2 border-t border-gray-100 mt-auto">
                                        @if ($esActual)
                                            <button type="button" disabled
                                                class="w-full px-6 py-3 bg-green-50 text-green-700 border-2 border-green-200 rounded-xl font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Plan actual
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn-pagar w-full px-6 py-3 {{ $destacado ? 'bg-[#FF6B00] hover:bg-orange-600 shadow-lg shadow-orange-500/30' : 'bg-[#0039A6] hover:bg-[#002a7a] shadow-lg shadow-blue-500/20' }} text-white rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2"
                                                data-plan-id="{{ $plan['id'] }}"
                                                data-plan-nombre="{{ $plan['nombre'] ?? 'Plan' }}"
                                                data-plan-precio="{{ $simbolo }}{{ number_format($plan['precio'] ?? 0, 2) }}">
                                                <i class="fa-solid fa-credit-card"></i>
                                                Pagar ahora
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Nota al pie --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-[#0039A6] mt-0.5"></i>
                            <p class="text-sm text-gray-700">
                                Los pagos se procesarán de forma segura. Si tienes dudas sobre qué plan elegir, puedes contactar con nuestro equipo de soporte.
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .plan-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .plan-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px -10px rgba(0, 27, 94, 0.15);
            }
            .plan-card.destacado {
                box-shadow: 0 10px 30px -10px rgba(255, 107, 0, 0.3);
            }
            .plan-card.destacado:hover {
                box-shadow: 0 25px 50px -10px rgba(255, 107, 0, 0.4);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.btn-pagar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const planId = this.dataset.planId;
                        const planNombre = this.dataset.planNombre;
                        const planPrecio = this.dataset.planPrecio;

                        showToast(
                            `Iniciando pago del plan "${planNombre}" (${planPrecio}) — próximamente`,
                            'info');
                        console.log('🛒 Plan a pagar:', { planId, planNombre, planPrecio });
                    });
                });

                function showToast(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-50 border-green-200 text-green-700',
                        error: 'bg-red-50 border-red-200 text-red-700',
                        info: 'bg-blue-50 border-blue-200 text-[#0039A6]',
                    };
                    const icons = {
                        success: 'fa-circle-check text-green-500',
                        error: 'fa-circle-exclamation text-red-500',
                        info: 'fa-circle-info text-[#0039A6]',
                    };

                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 border ${colors[type]}`;
                    toast.innerHTML = `
                        <i class="fa-solid ${icons[type]}"></i>
                        <span class="text-sm">${message}</span>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }, 3500);
                }
            });
        </script>
    @endpush
@endsection