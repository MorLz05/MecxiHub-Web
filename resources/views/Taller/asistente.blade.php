@extends('Taller.layouts.app')

@section('title', 'MecxiBot - Panel Taller')
@section('layout-main-class', 'overflow-hidden')

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
    <div class="mx-auto flex h-full min-h-0 max-w-6xl flex-col gap-5">
        <header class="flex shrink-0 flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Asistente para tu taller</p>
                <h1 class="mt-1 text-3xl font-black text-slate-900 sm:text-4xl">Mecxi<span class="text-orange-500">Bot</span></h1>
                <p class="mt-1 text-sm text-slate-500">Apoyo para consultas de órdenes y gestión del taller.</p>
            </div>
            <span class="rounded-full bg-green-100 px-3 py-1.5 text-xs font-semibold text-green-700">● Asistente listo</span>
        </header>

        <section id="mecxibot-chat"
            class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl"
            data-role="taller"
            data-authenticated="true"
            data-context-url="{{ route('asistente.context') }}"
            data-intent-url="{{ route('asistente.intent') }}"
            data-action-url="{{ route('taller.asistente.order-action') }}"
            data-login-url="{{ route('login') }}"
            data-initial-message="{{ session()->pull('ai_pending_message', '') }}">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-7">
                <h2 class="text-lg font-bold text-slate-900">Chat de asistencia mecánica</h2>
                <p class="mt-1 text-sm text-slate-500">Respuestas formales basadas en las órdenes de tu taller, sin datos de contacto de clientes.</p>
            </div>

            <div id="chat-error" role="alert" class="hidden mx-5 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:mx-7"></div>
            <div id="chat-messages" aria-live="polite" class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-5 sm:px-7">
                <article class="mr-auto max-w-[95%] rounded-2xl rounded-tl-sm border border-slate-200 bg-slate-50 px-5 py-4 text-slate-800 sm:max-w-[85%]">
                    <strong class="mb-2 block text-xs text-orange-600">🤖 MecxiBot</strong>
                    <p class="text-sm leading-relaxed">Buen día. Puedo ayudarte a consultar órdenes de tu taller, revisar pendientes y preparar propuestas de creación o edición. No guardaré cambios sin tu confirmación.</p>
                </article>
            </div>
            <div id="chat-attachments" class="hidden flex-wrap gap-2 border-t border-slate-100 px-4 py-2 sm:px-7"></div>
            <div id="chat-pending-action" class="hidden border-t border-amber-200 bg-amber-50 p-4 sm:p-6"></div>

            <form id="chat-form" class="shrink-0 border-t border-slate-200 bg-white p-3 sm:p-5">
                <input id="chat-files" type="file" accept="image/*,application/pdf" capture="environment" multiple class="hidden">
                <label class="sr-only" for="chat-input">Escribe tu mensaje para MecxiBot</label>
                <div class="flex items-end gap-1 sm:gap-2">
                    <button id="chat-attach" type="button" aria-label="Adjuntar imagen o PDF" class="rounded-xl p-3 text-slate-500 hover:bg-blue-50 hover:text-blue-700">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <button id="chat-mic" type="button" aria-label="Dictar mensaje" class="rounded-xl p-3 text-slate-500 hover:bg-blue-50 hover:text-blue-700">
                        <i class="fa-solid fa-microphone"></i>
                    </button>
                    <textarea id="chat-input" rows="1" maxlength="2000" placeholder="Escribe tu consulta..."
                        class="max-h-36 min-h-12 flex-1 resize-y rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"></textarea>
                    <button id="chat-send" type="submit" aria-label="Enviar mensaje" class="rounded-2xl bg-blue-700 p-4 text-white shadow-lg hover:bg-blue-800">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <p class="mt-2 text-center text-[10px] text-slate-400">Verifica las propuestas antes de confirmarlas. Las fotos no se adjuntan automáticamente a las órdenes.</p>
            </form>
        </section>
    </div>
@endsection
