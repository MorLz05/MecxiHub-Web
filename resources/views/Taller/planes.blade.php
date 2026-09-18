@extends('taller.layouts.app')

@section('title', 'Planes disponibles - MecxiHub')

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
            border-color: #FF8800;
            box-shadow: 0 10px 30px -10px rgba(255, 136, 0, 0.3);
        }

        .plan-card.destacado:hover {
            box-shadow: 0 25px 50px -10px rgba(255, 136, 0, 0.4);
        }
    </style>
@endpush

@section('content')

    {{-- Header de la página --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                    <span
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-orange to-orange-600 text-white flex items-center justify-center shadow-lg shadow-orange-500/30">
                        <i class="fa-solid fa-crown text-lg"></i>
                    </span>
                    Planes disponibles
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    Elige el plan que mejor se adapte a tu taller. Todos incluyen acceso a la plataforma MecxiHub.
                </p>
            </div>

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
        </div>
    </div>

    {{-- Contenido --}}
    @if (empty($planes))
        {{-- Estado vacío --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12">
            <div class="flex flex-col items-center text-center gap-3 max-w-md mx-auto">
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-crown text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Aún no hay planes disponibles</h3>
                <p class="text-sm text-gray-500">
                    En este momento no hay planes dirigidos a talleres. Vuelve a intentarlo más tarde o contacta con
                    soporte.
                </p>
                <a href="{{ route('taller.dashboard') }}"
                    class="mt-2 px-5 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver al panel
                </a>
            </div>
        </div>
    @else
        {{-- Grid de planes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($planes as $plan)
                @php
                    $esActual = $plan['es_plan_actual'] ?? false;
                    $destacado = !empty($plan['destacado']);
                    $moneda = $plan['moneda'] ?? 'USD';
                    $simbolos = ['USD' => '$', 'MXN' => '$', 'EUR' => '€'];
                    $simbolo = $simbolos[$moneda] ?? '$';
                    $caracteristicas = $plan['caracteristicas'] ?? [];
                @endphp

                <div
                    class="plan-card relative bg-white rounded-2xl border-2 {{ $destacado ? 'destacado border-brand-orange' : 'border-gray-200' }} shadow-sm overflow-hidden flex flex-col">

                    {{-- Ribbon si es destacado --}}
                    @if ($destacado)
                        <div
                            class="absolute top-0 right-0 bg-gradient-to-r from-brand-orange to-orange-500 text-white text-[10px] font-bold uppercase tracking-wider px-4 py-1.5 rounded-bl-xl shadow-md">
                            <i class="fa-solid fa-star text-[8px] mr-1"></i> Recomendado
                        </div>
                    @endif

                    {{-- Cabecera --}}
                    <div class="p-6 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-11 h-11 rounded-xl {{ $destacado ? 'bg-brand-orange/10 text-brand-orange' : 'bg-brand-blue/10 text-brand-blue' }} flex items-center justify-center">
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
                                        <span
                                            class="flex-shrink-0 w-5 h-5 rounded-full {{ $destacado ? 'bg-brand-orange/15 text-brand-orange' : 'bg-green-100 text-green-600' }} flex items-center justify-center mt-0.5">
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
                                class="btn-pagar w-full px-6 py-3 {{ $destacado ? 'bg-brand-orange hover:bg-orange-600 shadow-lg shadow-orange-500/30' : 'bg-brand-blue hover:bg-brand-darkblue shadow-lg shadow-blue-500/20' }} text-white rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2"
                                data-plan-id="{{ $plan['id'] }}" data-plan-nombre="{{ $plan['nombre'] ?? 'Plan' }}"
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
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-brand-blue mt-0.5"></i>
            <p class="text-sm text-gray-700">
                Los pagos se procesarán de forma segura. Si tienes dudas sobre qué plan elegir, puedes contactar con nuestro
                equipo de soporte.
            </p>
        </div>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.btn-pagar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const planId = this.dataset.planId;
                        const planNombre = this.dataset.planNombre;
                        const planPrecio = this.dataset.planPrecio;

                        // Toast informativo mientras no hay pasarela de pago
                        showToast(
                            `Iniciando pago del plan "${planNombre}" (${planPrecio}) — próximamente`,
                            'info');
                        console.log('🛒 Plan a pagar:', {
                            planId,
                            planNombre,
                            planPrecio
                        });
                    });
                });

                function showToast(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-50 border-green-200 text-green-700',
                        error: 'bg-red-50 border-red-200 text-red-700',
                        info: 'bg-blue-50 border-blue-200 text-brand-blue',
                    };
                    const icons = {
                        success: 'fa-circle-check text-green-500',
                        error: 'fa-circle-exclamation text-red-500',
                        info: 'fa-circle-info text-brand-blue',
                    };

                    const toast = document.createElement('div');
                    toast.className =
                        `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 border ${colors[type]}`;
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
