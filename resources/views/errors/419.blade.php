@extends('conductor.layouts.app')
@section('title', 'Sesión expirada - MecxiHub')
@section('content')
    <div class="min-h-[calc(100vh-80px)] bg-white flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full text-center">
            <div class="w-24 h-24 bg-yellow-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fa-solid fa-clock-rotate-left text-5xl text-yellow-500"></i>
            </div>
            <h1 class="text-3xl font-black mb-2">
                <span class="text-[#0039A6]">Sesión</span> <span class="text-[#FF6B00]">Expirada</span>
            </h1>
            <p class="text-gray-600 text-sm mb-8">
                Tu sesión ha estado inactiva por mucho tiempo por tu seguridad. Por favor, vuelve a iniciar sesión para continuar.
            </p>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl bg-[#0039A6] hover:bg-[#002B80] text-white font-semibold text-base shadow-lg transition">
                <i class="fa-solid fa-right-to-bracket"></i> Volver al Login
            </a>
        </div>
    </div>
@endsection
