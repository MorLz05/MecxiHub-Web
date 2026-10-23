@extends('conductor.layouts.app')

@section('title', 'Asistente IA - MecxiHub')
@section('layout-main-class', 'h-[calc(100dvh-6rem)] overflow-hidden')

@php
    $firebaseAiConfig = [
        'firebaseConfig' => [
            'apiKey' => config('firebase_ai.web.api_key'),
            'authDomain' => config('firebase_ai.web.auth_domain'),
            'projectId' => config('firebase_ai.web.project_id'),
            'storageBucket' => config('firebase_ai.web.storage_bucket'),
            'messagingSenderId' => config('firebase_ai.web.messaging_sender_id'),
            'appId' => config('firebase_ai.web.app_id'),
        ],
        'recaptchaSiteKey' => config('firebase_ai.app_check.recaptcha_site_key'),
        'debug' => app()->environment('local') && config('firebase_ai.app_check.debug'),
    ];
@endphp

@push('scripts')
    <script id="firebase-ai-config" type="application/json">@json($firebaseAiConfig)</script>
    @vite('resources/js/firebase-ai-chat.js')
@endpush

@section('content')
    <section class="relative h-28 shrink-0 overflow-hidden bg-[#002677] px-4 py-4 text-white sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex h-full items-center gap-5">
                <img src="{{ asset('images/logo/logo2.png') }}" alt="MecxiHub" class="h-12 w-auto">
                <div>
                    <p class="text-xs font-semibold text-blue-100">Asistente para conductores</p>
                    <h1 class="text-2xl font-black sm:text-3xl">Mecxi<span class="text-[#FF6B00]">Bot</span></h1>
                    <p class="hidden max-w-xl text-sm text-blue-100 sm:block">Cuéntame qué le pasa a tu vehículo y te oriento con un tono claro y cercano.</p>
                </div>
            </div>
        </div>
        <div class="absolute -right-12 -top-20 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
    </section>

    <div class="mx-auto flex h-[calc(100%-7rem)] min-h-0 w-full max-w-7xl flex-col gap-4 px-4 py-4 lg:flex-row">
        <section id="mecxibot-chat"
            class="relative z-10 flex min-h-0 flex-1 flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white p-4 shadow-2xl sm:p-6"
            data-role="conductor"
            data-log-url="{{ route('asistente.log') }}"
            data-chat-session="{{ session()->has('firebase_user') ? \App\Http\Controllers\AiAssistantController::chatSessionToken() : '' }}"
            data-authenticated="{{ session()->has('firebase_user') ? 'true' : 'false' }}"
            data-context-url="{{ route('asistente.context') }}"
            data-intent-url="{{ route('asistente.intent') }}"
            data-action-url="{{ route('taller.asistente.order-action') }}"
            data-login-url="{{ route('login') }}"
            data-initial-message="{{ session()->pull('ai_pending_message', '') }}">
            <div class="mb-2 flex shrink-0 items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Chat con MecxiBot</h2>
                    <p class="mt-1 text-sm text-gray-500">Orientación basada únicamente en los vehículos y servicios de tu cuenta.</p>
                </div>
                <span class="whitespace-nowrap rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">● En línea</span>
            </div>
            <div id="chat-error" role="alert" class="hidden mb-2 shrink-0 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
            <div id="chat-messages" aria-live="polite" class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain rounded-2xl bg-white px-1 py-3">
                <article class="mr-auto max-w-[92%] rounded-2xl rounded-tl-sm border border-gray-200 bg-gray-100 px-5 py-4 text-gray-800">
                    <strong class="mb-2 block text-xs text-[#FF6B00]">🤖 MecxiBot</strong>
                    <p class="text-sm leading-relaxed">¡Hola! Cuéntame qué ruido, falla o duda tienes. Si tienes varios vehículos en tu cuenta, dime cuál quieres consultar.</p>
                </article>
            </div>
            <div id="chat-attachments" class="hidden shrink-0 flex-wrap gap-2 border-t border-gray-100 py-2"></div>
            <div id="chat-pending-action" class="hidden shrink-0 rounded-xl border border-amber-300 bg-amber-50 p-4"></div>
            <form id="chat-form" class="mt-2 shrink-0 border-t border-gray-100 pt-3">
                <input id="chat-files" type="file" accept="image/*,application/pdf" capture="environment" multiple class="hidden">
                <label class="sr-only" for="chat-input">Escribe tu mensaje</label>
                <div class="flex items-end gap-2">
                    <button id="chat-attach" type="button" aria-label="Adjuntar foto o PDF" class="rounded-xl p-3 text-gray-500 hover:bg-blue-50 hover:text-[#0039A6]">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <button id="chat-mic" type="button" aria-label="Dictar mensaje" class="rounded-xl p-3 text-gray-500 hover:bg-blue-50 hover:text-[#0039A6]">
                        <i class="fa-solid fa-microphone"></i>
                    </button>
                    <textarea id="chat-input" rows="2" maxlength="2000" placeholder="Escribe tu mensaje aquí..."
                        class="mecxibot-chat-input min-h-0 flex-1 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none focus:border-[#0039A6] focus:ring-2 focus:ring-blue-100"></textarea>
                    <button id="chat-send" type="submit" aria-label="Enviar mensaje" class="rounded-2xl bg-[#FF6B00] p-4 text-white shadow-lg shadow-orange-500/30 hover:bg-orange-600">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <p class="mt-2 text-center text-[10px] text-gray-400">MecxiBot puede equivocarse. Para fallas de seguridad, solicita una revisión profesional.</p>
            </form>
        </section>

        <aside class="relative z-10 hidden w-80 shrink-0 space-y-4 overflow-y-auto lg:block">
            <section class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 font-bold text-gray-900"><i class="fa-solid fa-lightbulb text-[#FF6B00]"></i> Preguntas frecuentes</h2>
                <div class="space-y-2">
                    @foreach ([
                        '¿Por qué mi auto hace ese ruido?',
                        '¿Cada cuánto cambiar las pastillas de freno?',
                        '¿Qué significa que el auto se vaya de lado?',
                        '¿Cómo puedo mejorar el rendimiento?',
                    ] as $question)
                        <button type="button" data-quick-question="{{ $question }}"
                            class="w-full rounded-xl border border-gray-200 p-3 text-left text-sm text-gray-700 transition hover:border-[#FF6B00] hover:bg-orange-50">
                            <i class="fa-solid fa-car-side mr-2 text-[#0039A6]"></i>{{ $question }}
                        </button>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-gray-500">Algunos consejos dependen del modelo y año. MecxiBot te preguntará si necesita ese dato.</p>
            </section>
            <section class="rounded-2xl border border-blue-200 bg-white p-5 text-sm text-gray-600 shadow-sm">
                <h2 class="mb-2 font-bold text-gray-900"><i class="fa-solid fa-shield-halved mr-2 text-[#FF6B00]"></i>Tu información protegida</h2>
                <p>Para personalizar una respuesta, el asistente consulta solo tus vehículos y servicios vinculados a tu cuenta. No consulta órdenes de otros clientes ni talleres.</p>
            </section>
        </aside>
    </div>
@endsection
