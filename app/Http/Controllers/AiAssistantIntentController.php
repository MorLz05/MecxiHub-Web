<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiAssistantIntentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);
        session(['ai_pending_message' => $validated['message']]);

        if (session()->has('firebase_user')) {
            $route = session('firebase_user.rol') === 'Administrador' ? 'taller.asistente' : 'asistente.ia';
            return redirect()->route($route);
        }

        session(['url.intended' => route('asistente.ia')]);
        return redirect()->route('login');
    }
}
