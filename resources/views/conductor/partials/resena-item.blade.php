@php
    $nombre = $resena['usuario_nombre'] ?? 'Cliente';
    $iniciales = $resena['usuario_inicial'] ?? strtoupper(mb_substr($nombre, 0, 1));
    $rating = (int) ($resena['rating'] ?? 0);
    $fecha = !empty($resena['fecha'])
        ? \Carbon\Carbon::parse($resena['fecha'])->diffForHumans()
        : 'Reciente';
@endphp

<div class="p-5 rounded-2xl bg-gray-50/80 border border-gray-100">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0039A6] to-[#0066FF] flex items-center justify-center flex-shrink-0 shadow-sm">
            <span class="text-white font-bold text-sm">{{ $iniciales }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ $nombre }}</p>
                    <p class="text-xs text-gray-400">{{ $fecha }}</p>
                </div>
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-sm {{ $i <= $rating ? 'text-[#FF6B00]' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
            </div>

            @if (!empty($resena['comentario']))
                <p class="text-gray-600 text-sm mt-3 leading-relaxed">
                    {{ $resena['comentario'] }}
                </p>
            @endif

            @if (!empty($resena['respuesta_taller']))
                <div class="mt-4 pl-4 border-l-2 border-[#0039A6]/30">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-semibold text-[#0039A6]">Respuesta del taller</span>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        {{ $resena['respuesta_taller'] }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
