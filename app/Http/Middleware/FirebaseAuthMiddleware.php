<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirebaseAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('firebase_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        return $next($request);
    }
}
