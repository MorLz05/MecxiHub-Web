<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AccountLink extends Component
{
    public function getRoute()
    {
        return session('firebase_user.rol', 'Conductor') === 'GestorMaestro'
            ? 'gestor.cuenta'
            : 'mi.cuenta';
    }

    public function getLabel()
    {
        return session('firebase_user.rol', 'Conductor') === 'GestorMaestro'
            ? 'Panel Gestor'
            : 'Mi cuenta';
    }

    public function render()
    {
        return view('components.account-link');
    }
}
