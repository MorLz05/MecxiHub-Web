@extends('taller.layouts.app')

@section('title', 'Resumen - Panel Taller')

@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Título de página -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Panel de tu taller</h1>
                <p class="text-gray-500 text-sm mt-1">Resumen general de tu taller y sus actividades.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- === TARJETA PRINCIPAL DEL TALLER === -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-[#0039A6]/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-store text-3xl text-[#0039A6]"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900">{{ session('firebase_user.nombre_completo') }}</h2>
                                        <div class="flex items-center gap-3 mt-1">
                                            <!-- Verificado -->
                                            @php
                                                // Obtener datos del taller de la sesión o Firestore
                                                // Por ahora usamos datos de sesión
                                                $taller = session('taller_data', [
                                                    'verificado' => false,
                                                    'plan' => null
                                                ]);
                                            @endphp
                                            @if($taller['verificado'] ?? false)
                                                <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-0.5 rounded-full text-xs font-semibold">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Verificado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 px-3 py-0.5 rounded-full text-xs font-semibold">
                                                    <i class="fa-solid fa-clock"></i>
                                                    En verificación
                                                </span>
                                            @endif
                                            <!-- Plan -->
                                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-[#0039A6] px-3 py-0.5 rounded-full text-xs font-semibold">
                                                <i class="fa-solid fa-crown"></i>
                                                {{ $taller['plan'] ?? 'Sin plan' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- === TARJETA DE ESTADÍSTICAS RÁPIDAS === -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Estadísticas rápidas</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clipboard-list text-[#0039A6]"></i>
                                    <span class="text-sm text-gray-600">Órdenes totales</span>
                                </div>
                                <span class="font-bold text-[#0039A6]">0</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-green-600"></i>
                                    <span class="text-sm text-gray-600">Completadas</span>
                                </div>
                                <span class="font-bold text-green-600">0</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock text-yellow-600"></i>
                                    <span class="text-sm text-gray-600">Pendientes</span>
                                </div>
                                <span class="font-bold text-yellow-600">0</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-star text-[#FF6B00]"></i>
                                    <span class="text-sm text-gray-600">Calificación</span>
                                </div>
                                <span class="font-bold text-[#FF6B00]">--</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- === SECCIÓN DE ACTIVIDAD RECIENTE === -->
            <div class="mt-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Actividad reciente</h3>
                        <span class="text-xs text-gray-400">Últimos 30 días</span>
                    </div>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-inbox text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500">No hay actividad reciente</p>
                        <p class="text-xs text-gray-400">Cuando recibas órdenes, aparecerán aquí</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
