<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckGestorRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('firebase_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        $userData = session('firebase_user');
        $userRole = $userData['rol'] ?? '';

        Log::info('Verificando rol de usuario: ' . $userRole);

        // SOLO GestorMaestro puede acceder
        if ($userRole !== 'GestorMaestro') {
            Log::warning('Usuario con rol ' . $userRole . ' intentó acceder a área de gestor');
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder al panel de gestor.');
        }

        return $next($request);
    }
}
