<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckGestorRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Registrar middleware con alias
        $middleware->alias([
            'auth.firebase' => \App\Http\Middleware\FirebaseAuthMiddleware::class,
            'gestor' => CheckGestorRole::class,
            'taller' => \App\Http\Middleware\TallerMiddleware::class,
        ]);

        // Si quieres agregar middleware a grupos existentes
        // $middleware->appendToGroup('web', FirebaseAuth::class);

        // O si quieres que sea middleware global (NO recomendado para auth)
        // $middleware->append(FirebaseAuth::class);
    })

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
