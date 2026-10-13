<?php

use App\Http\Middleware\EnsureRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias 'rol' para el guardia de acceso por rol (RN-01).
        $middleware->alias([
            'rol' => EnsureRol::class,
        ]);

        // Al entrar sin sesion, la plataforma devuelve al formulario de ingreso.
        $middleware->redirectGuestsTo(fn () => route('ingresar'));

        // Render termina el HTTPS en su proxy y reenvia la peticion por HTTP.
        // Confiar en sus cabeceras X-Forwarded-* hace que Laravel genere las
        // URLs con https y el navegador no bloquee el CSS por contenido mixto.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
