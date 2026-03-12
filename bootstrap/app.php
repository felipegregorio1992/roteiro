<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware de segurança global
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Rate limiting usando cache padrão (sem Redis)
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
