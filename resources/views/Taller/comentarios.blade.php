@extends('taller.layouts.app')

@section('title', 'Comentarios - ' . ($taller['nombre'] ?? 'Panel Taller'))

@section('content')
    {{-- HEADER --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Comentarios y reseñas</h1>
            <p class="text-sm text-gray-500 mt-1">
                Gestiona las opiniones de tus clientes y responde para mejorar tu reputación.
            </p>
        </div>
        <a href="{{ route('taller.dashboard') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
            Volver al resumen
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
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

    {{-- GRID: RESUMEN + TARJETAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Card: Calificación promedio --}}
        <div class="bg-gradient-to-br from-brand-darkblue to-[#001c59] rounded-3xl shadow-xl p-6 text-white relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-brand-orange/10 rounded-full"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-star text-brand-orange text-lg"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Calificación general</span>
                </div>

                <div class="flex items-end gap-3">
                    <p class="text-6xl font-black leading-none">{{ number_format($promedio, 1) }}</p>
                    <div class="pb-1.5">
                        <div class="text-brand-orange flex text-lg gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= round($promedio) ? '' : 'text-white/20' }}"></i>
                            @endfor
                        </div>
                        <p class="text-blue-200 text-xs mt-1.5">
                            {{ $totalResenas }} {{ $totalResenas === 1 ? 'reseña' : 'reseñas' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Respondidas --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Respondidas</p>
                    <p class="text-4xl font-black text-gray-900 mt-2">{{ $respondidas }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        de {{ $totalResenas }} {{ $totalResenas === 1 ? 'reseña' : 'reseñas' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                    <i class="fa-solid fa-comment-dots text-green-600 text-xl"></i>
                </div>
            </div>

            @if ($totalResenas > 0)
                <div class="mt-4 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all"
                         style="width: {{ $totalResenas > 0 ? round(($respondidas / $totalResenas) * 100) : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">
                    {{ $totalResenas > 0 ? round(($respondidas / $totalResenas) * 100) : 0 }}% del total
                </p>
            @endif
        </div>

        {{-- Card: Pendientes --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Sin responder</p>
                    <p class="text-4xl font-black {{ $sinResponder > 0 ? 'text-brand-orange' : 'text-gray-900' }} mt-2">
                        {{ $sinResponder }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $sinResponder === 1 ? 'reseña pendiente' : 'reseñas pendientes' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-brand-orange text-xl"></i>
                </div>
            </div>

            @if ($sinResponder > 0)
                <a href="{{ route('taller.comentarios', ['estado' => 'pendientes']) }}"
                   class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-orange hover:underline">
                    Responder ahora
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            @else
                <p class="mt-4 text-xs text-gray-400">¡Todo al día! 🎉</p>
            @endif
        </div>

    </div>

    {{-- DISTRIBUCIÓN DE ESTRELLAS --}}
    @if ($totalResenas > 0)
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-8">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-brand-blue"></i>
                Distribución de calificaciones
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                @foreach ([5, 4, 3, 2, 1] as $est)
                    <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-600">{{ $est }}★</span>
                            <span class="text-xs text-gray-400">{{ $porcentajes[$est] ?? 0 }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-brand-orange rounded-full transition-all"
                                 style="width: {{ $porcentajes[$est] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <strong class="text-gray-700">{{ $distribucion[$est] ?? 0 }}</strong>
                            {{ ($distribucion[$est] ?? 0) === 1 ? 'reseña' : 'reseñas' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filtrar:</span>

        <a href="{{ route('taller.comentarios') }}"
           class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition
                  {{ !$filtroEstado && !$filtroRating ? 'bg-brand-darkblue text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Todas
        </a>

        <a href="{{ route('taller.comentarios', ['estado' => 'pendientes']) }}"
           class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition
                  {{ $filtroEstado === 'pendientes' ? 'bg-brand-orange text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Sin responder @if($sinResponder > 0) <span class="ml-1 bg-white/30 px-1.5 rounded-full">{{ $sinResponder }}</span> @endif
        </a>

        <a href="{{ route('taller.comentarios', ['estado' => 'respondidas']) }}"
           class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition
                  {{ $filtroEstado === 'respondidas' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Respondidas
        </a>

        <div class="h-5 w-px bg-gray-200 mx-1"></div>

        @foreach ([5, 4, 3, 2, 1] as $est)
            <a href="{{ route('taller.comentarios', ['rating' => $est]) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold transition flex items-center gap-1
                      {{ (int)$filtroRating === $est ? 'bg-brand-darkblue text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $est }} <i class="fa-solid fa-star text-[9px] text-brand-orange"></i>
            </a>
        @endforeach

        @if ($filtroEstado || $filtroRating)
            <a href="{{ route('taller.comentarios') }}"
               class="ml-auto inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 font-semibold">
                <i class="fa-solid fa-xmark"></i> Limpiar filtros
            </a>
        @endif
    </div>

    {{-- LISTA DE RESEÑAS --}}
    @if (empty($resenas))
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-regular fa-comment-dots text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">No hay reseñas para mostrar</h3>
            <p class="text-gray-500 text-sm mt-1">
                @if ($filtroEstado || $filtroRating)
                    Prueba con otros filtros.
                @else
                    Cuando tus clientes califiquen sus servicios, aparecerán aquí.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($resenas as $resena)
                @php
                    $nombre = $resena['usuario_nombre'] ?? 'Cliente';
                    $iniciales = $resena['usuario_inicial'] ?? strtoupper(mb_substr($nombre, 0, 1));
                    $rating = (int) ($resena['rating'] ?? 0);
                    $fecha = !empty($resena['fecha'])
                        ? \Carbon\Carbon::parse($resena['fecha'])->diffForHumans()
                        : 'Reciente';
                    $tieneRespuesta = !empty($resena['respuesta_taller']);
                    $respuestaFecha = !empty($resena['respuesta_fecha'])
                        ? \Carbon\Carbon::parse($resena['respuesta_fecha'])->diffForHumans()
                        : null;
                @endphp

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition"
                     x-data="{ responder: false, editando: false }">

                    {{-- Cabecera: usuario + estrellas --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-blue to-brand-darkblue flex items-center justify-center flex-shrink-0 shadow-sm">
                            <span class="text-white font-bold text-sm">{{ $iniciales }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $nombre }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $fecha }}</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star text-sm {{ $i <= $rating ? 'text-brand-orange' : 'text-gray-200' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 ml-1">{{ $rating }}.0</span>
                                </div>
                            </div>

                            {{-- Comentario --}}
                            @if (!empty($resena['comentario']))
                                <p class="text-gray-700 text-sm mt-3 leading-relaxed">
                                    {{ $resena['comentario'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Respuesta existente --}}
                    @if ($tieneRespuesta)
                        <div class="mt-4 ml-16 pl-4 border-l-3 border-brand-blue bg-blue-50/40 rounded-r-xl p-4"
                             style="border-left-width: 3px;">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-6 h-6 rounded-full bg-brand-darkblue flex items-center justify-center">
                                    <i class="fa-solid fa-store text-white text-[10px]"></i>
                                </div>
                                <span class="text-xs font-bold text-brand-darkblue">Tu respuesta</span>
                                @if ($respuestaFecha)
                                    <span class="text-xs text-gray-400">• {{ $respuestaFecha }}</span>
                                @endif
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed">{{ $resena['respuesta_taller'] }}</p>

                            <div class="flex items-center gap-3 mt-3">
                                <button @click="editando = !editando"
                                        class="text-xs text-brand-blue hover:underline font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-pen"></i>
                                    <span x-text="editando ? 'Cancelar' : 'Editar respuesta'"></span>
                                </button>

                                <form action="{{ route('taller.comentarios.respuesta.eliminar', ['resenaId' => $resena['id']]) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar esta respuesta?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-500 hover:underline font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </div>

                            {{-- Form editar (oculto por defecto) --}}
                            <div x-show="editando" x-transition class="mt-3">
                                <form action="{{ route('taller.comentarios.responder', ['resenaId' => $resena['id']]) }}"
                                      method="POST">
                                    @csrf
                                    <textarea name="respuesta" rows="3" maxlength="1000"
                                              class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 resize-none">{{ $resena['respuesta_taller'] }}</textarea>
                                    <div class="flex justify-end gap-2 mt-2">
                                        <button type="button" @click="editando = false"
                                                class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-xl bg-brand-blue text-white text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1.5">
                                            <i class="fa-solid fa-check"></i>
                                            Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Sin respuesta: botón + form --}}
                        <div class="mt-4 ml-16">
                            <button @click="responder = !responder"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-orange text-white text-xs font-semibold hover:bg-orange-600 transition shadow-sm">
                                <i class="fa-solid fa-reply"></i>
                                <span x-text="responder ? 'Cancelar' : 'Responder'"></span>
                            </button>

                            <div x-show="responder" x-transition class="mt-3">
                                <form action="{{ route('taller.comentarios.responder', ['resenaId' => $resena['id']]) }}"
                                      method="POST">
                                    @csrf
                                    <textarea name="respuesta" rows="3" maxlength="1000" required
                                              placeholder="Gracias por tu comentario..."
                                              class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 resize-none"></textarea>
                                    <div class="flex justify-end gap-2 mt-2">
                                        <button type="button" @click="responder = false"
                                                class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-xl bg-brand-blue text-white text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1.5">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            Publicar respuesta
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

    {{-- Nota informativa --}}
    <div class="mt-8 p-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-lightbulb text-brand-blue text-sm"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-brand-darkblue">Consejo</p>
            <p class="text-xs text-gray-600 mt-0.5">
                Responder a las reseñas (especialmente las negativas) mejora tu reputación y ayuda a futuros clientes a confiar en tu taller.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Confirmación opcional: ya está con onsubmit
    // Aquí puedes agregar lógica extra si lo necesitas
</script>
@endpush
