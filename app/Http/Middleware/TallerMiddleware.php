<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TallerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('firebase_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        $user = session('firebase_user');
        $rol = $user['rol'] ?? '';

        if ($rol !== 'Administrador') {
            Log::warning('Intento de acceso a ruta de taller por usuario no autorizado: ' . $user['email']);
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
