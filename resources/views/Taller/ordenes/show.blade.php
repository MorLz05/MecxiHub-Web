@extends('taller.layouts.app')

@section('title', 'Orden ' . ($orden['folio'] ?? '') . ' - MecxiHub')

@section('content')

    @php
        $estado = $orden['estado'] ?? 'Pendiente';
        $colores = [
            'Pendiente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'En reparación' => 'bg-blue-100 text-brand-blue border-blue-200',
            'Listo' => 'bg-green-100 text-green-700 border-green-200',
            'Cancelado' => 'bg-red-100 text-red-700 border-red-200',
        ];
        $colorBadge = $colores[$estado] ?? 'bg-gray-100 text-gray-600 border-gray-200';
        $vehiculo = $orden['vehiculo'] ?? [];
        $presupuesto = $orden['presupuesto'] ?? [];
    @endphp

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('taller.ordenes') }}" class="text-sm text-gray-500 hover:text-brand-blue transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver a órdenes
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-2 flex items-center gap-3 flex-wrap">
                <span class="font-mono text-lg bg-brand-blue/10 text-brand-blue px-3 py-1.5 rounded-lg">
                    {{ $orden['folio'] ?? 'S/F' }}
                </span>
                <span class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $colorBadge }}">
                    {{ $estado }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('taller.ordenes.imprimir', $orden['id']) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-5 py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-bold text-sm shadow-lg shadow-gray-500/30 transition">
                <i class="fa-solid fa-print"></i> Imprimir
            </a>

            @if (!in_array($estado, ['Cancelado', 'Listo'], true))
                <a href="{{ route('taller.ordenes.editar', $orden['id']) }}"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 transition">
                    <i class="fa-solid fa-pen"></i> Editar orden
                </a>
            @endif
        </div>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            {{-- Cliente --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-brand-blue"></i> Cliente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Nombre</p>
                        <p class="font-medium text-gray-900">{{ $orden['cliente_nombre'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Email</p>
                        <p class="text-gray-700 text-sm">{{ $orden['cliente_email'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Teléfono</p>
                        <p class="text-gray-700 text-sm">{{ $orden['cliente_telefono'] ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Vehículo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-car text-brand-blue"></i> Vehículo
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Marca / Modelo</p>
                        <p class="font-medium text-gray-900">{{ $vehiculo['marca'] ?? '' }}
                            {{ $vehiculo['modelo'] ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Año</p>
                        <p class="text-gray-700 text-sm">{{ $vehiculo['anio'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Color</p>
                        <p class="text-gray-700 text-sm">{{ $vehiculo['color'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Placas</p>
                        <p class="font-mono text-sm text-gray-900 bg-gray-100 px-2 py-0.5 rounded inline-block">
                            {{ strtoupper($vehiculo['placas'] ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Kilometraje</p>
                        <p class="text-gray-700 text-sm">{{ number_format($vehiculo['kilometraje'] ?? 0) }} km</p>
                    </div>
                </div>
            </div>

            @php
                $evidenciasVehiculo = collect($evidencias ?? [])
                    ->where('tipo', 'vehiculo')
                    ->values();
            @endphp

            @if ($evidenciasVehiculo->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-camera text-brand-blue"></i>
                        Evidencias del vehículo ({{ $evidenciasVehiculo->count() }})
                    </h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                        @foreach ($evidenciasVehiculo as $ev)
                            <a href="{{ $ev['url'] }}" target="_blank" rel="noopener"
                                class="block aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100 hover:border-brand-blue hover:shadow-md transition group">
                                <img src="{{ $ev['url'] }}" alt="Evidencia"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Fallas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench text-brand-orange"></i> Fallas ({{ count($fallas) }})
                </h3>

                @if (!empty($fallas))
                    <div class="space-y-3">
                        @foreach ($fallas as $i => $falla)
                            @php
                                $prio = $falla['prioridad'] ?? 'Media';
                                $prioColores = [
                                    'Baja' => 'bg-green-100 text-green-700',
                                    'Media' => 'bg-blue-100 text-brand-blue',
                                    'Alta' => 'bg-orange-100 text-brand-orange',
                                    'Urgente' => 'bg-red-100 text-red-700',
                                ];
                            @endphp

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="text-sm font-bold text-gray-800">{{ $falla['categoria'] ?? 'Sin categoría' }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $prioColores[$prio] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $prio }}
                                        </span>
                                    </div>
                                    <span class="font-bold text-gray-900 whitespace-nowrap">
                                        ${{ number_format($falla['precio'] ?? 0, 2) }}
                                    </span>
                                </div>

                                @if (!empty($falla['descripcion']))
                                    <p class="text-sm text-gray-600 leading-relaxed">{{ $falla['descripcion'] }}</p>
                                @endif

                                @php
                                    $evidenciasFalla = collect($evidencias ?? [])
                                        ->where('tipo', 'falla')
                                        ->where('falla_index', $i)
                                        ->values();
                                @endphp

                                @if ($evidenciasFalla->isNotEmpty())
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p
                                            class="text-[11px] text-gray-500 uppercase font-semibold mb-2 flex items-center gap-1">
                                            <i class="fa-solid fa-camera text-brand-blue"></i>
                                            Evidencias ({{ $evidenciasFalla->count() }})
                                        </p>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                            @foreach ($evidenciasFalla as $ev)
                                                <a href="{{ $ev['url'] }}" target="_blank" rel="noopener"
                                                    class="block aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100 hover:border-brand-blue hover:shadow-md transition group">
                                                    <img src="{{ $ev['url'] }}" alt="Evidencia"
                                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Sin fallas registradas.</p>
                @endif
            </div>

            {{-- Asignación de responsables (interno) --}}
            @php
                $asignacion = $orden['asignacion'] ?? [];
                $modoAsignacion = $asignacion['modo'] ?? 'none';
                $respGeneral = $asignacion['responsable_general'] ?? null;
            @endphp

            @if ($modoAsignacion !== 'none')
                <div class="bg-white rounded-2xl shadow-sm border border-purple-200 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <h3 class="text-sm font-bold text-purple-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-purple-600"></i> Asignación de responsables
                        </h3>
                        <span
                            class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold">INTERNO</span>
                    </div>

                    @if ($modoAsignacion === 'general' && $respGeneral)
                        <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl border border-purple-200">
                            <div
                                class="w-10 h-10 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold">
                                {{ mb_strtoupper(mb_substr($respGeneral['nombre'] ?? 'R', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs text-purple-600 uppercase font-semibold">Responsable general</p>
                                <p class="font-bold text-gray-900">{{ $respGeneral['nombre'] }}</p>
                            </div>
                        </div>
                    @elseif ($modoAsignacion === 'por_falla')
                        <div class="space-y-2">
                            @foreach ($fallas as $i => $falla)
                                @php $respFalla = $falla['responsable'] ?? null; @endphp
                                <div
                                    class="flex items-center justify-between gap-3 p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span
                                            class="text-sm text-gray-800 truncate">{{ $falla['categoria'] ?? '-' }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-purple-700 whitespace-nowrap">
                                        @if ($respFalla)
                                            <i class="fa-solid fa-user text-[10px]"></i>
                                            {{ $respFalla['nombre'] }}
                                        @else
                                            <span class="text-gray-400 italic text-xs">Sin asignar</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- columna derecha --}}
        <div class="space-y-6">

            {{-- Resumen económico --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-dollar-sign text-green-600"></i> Resumen económico
                </h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Costo estimado</span>
                        <span
                            class="font-semibold text-gray-800">${{ number_format($presupuesto['costo_estimado'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Anticipo</span>
                        <span
                            class="font-semibold text-gray-800">${{ number_format($presupuesto['anticipo'] ?? 0, 2) }}</span>
                    </div>
                    @if (!empty($presupuesto['metodo_pago']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Método de pago</span>
                            <span class="text-sm text-gray-800">{{ $presupuesto['metodo_pago'] }}</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-800">Precio final</span>
                        <span
                            class="text-xl font-extrabold text-brand-orange">${{ number_format($orden['precio_final'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-brand-blue"></i> Fechas
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Fecha de orden</p>
                        <p class="text-sm text-gray-800">
                            {{ isset($orden['fecha_orden']) ? \Carbon\Carbon::parse($orden['fecha_orden'])->format('d/m/Y H:i') : '-' }}
                        </p>
                    </div>

                    @if ($estado === 'Listo' && !empty($orden['fecha_entrega_real']))
                        <div>
                            <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Fecha de entrega</p>
                            <p class="text-sm text-green-700 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-green-500 text-xs"></i>
                                {{ \Carbon\Carbon::parse($orden['fecha_entrega_real'])->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="text-[11px] text-gray-500 uppercase font-semibold mb-1">Entrega estimada</p>
                            <p class="text-sm text-gray-800">
                                {{ isset($orden['fecha_entrega_estimada']) ? \Carbon\Carbon::parse($orden['fecha_entrega_estimada'])->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cambiar estado --}}
            @if (!empty($transiciones))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-flag text-brand-orange"></i> Acciones
                    </h3>
                    <div class="space-y-2">
                        @foreach ($transiciones as $est)
                            @php
                                $btnClass =
                                    $est === 'Cancelado'
                                        ? 'bg-red-50 hover:bg-red-100 text-red-700 border border-red-200'
                                        : 'bg-brand-blue hover:bg-brand-darkblue text-white';
                            @endphp
                            <button type="button"
                                class="btn-cambiar-estado w-full px-4 py-2.5 rounded-xl font-semibold text-sm transition {{ $btnClass }}"
                                data-estado="{{ $est }}">
                                @if ($est === 'Cancelado')
                                    <i class="fa-solid fa-ban mr-2"></i> Cancelar orden
                                @else
                                    <i class="fa-solid fa-arrow-right mr-2"></i> Pasar a "{{ $est }}"
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const csrf = '{{ csrf_token() }}';

                document.querySelectorAll('.btn-cambiar-estado').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const nuevoEstado = this.dataset.estado;
                        const esCancelar = nuevoEstado === 'Cancelado';

                        if (esCancelar && !confirm(
                                '¿Cancelar esta orden? Esta acción no se puede deshacer.')) return;
                        if (!esCancelar && !confirm('¿Cambiar el estado a "' + nuevoEstado + '"?'))
                            return;

                        this.disabled = true;

                        fetch('{{ route('taller.ordenes.cambiar-estado', $orden['id']) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    estado: nuevoEstado
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast(data.message, 'success');
                                    setTimeout(() => location.reload(), 700);
                                } else {
                                    showToast(data.error || 'Error', 'error');
                                    this.disabled = false;
                                }
                            })
                            .catch(() => {
                                showToast('Error de conexión', 'error');
                                this.disabled = false;
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
                    toast.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span class="text-sm">${message}</span>`;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }, 3000);
                }
            });
        </script>
    @endpush
@endsection
