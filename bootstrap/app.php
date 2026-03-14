<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você tem acesso apenas de leitura e não pode alterar este roteiro.',
                ], 403);
            }

            $previous = url()->previous();
            $target = $previous && $previous !== $request->fullUrl()
                ? $previous
                : route('dashboard');

            $redirect = redirect()->to($target)->with('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');

            if (! $request->isMethod('get')) {
                $redirect->withInput();
            }

            return $redirect;
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você tem acesso apenas de leitura e não pode alterar este roteiro.',
                ], 403);
            }

            $previous = url()->previous();
            $target = $previous && $previous !== $request->fullUrl()
                ? $previous
                : route('dashboard');

            $redirect = redirect()->to($target)->with('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');

            if (! $request->isMethod('get')) {
                $redirect->withInput();
            }

            return $redirect;
        });
    })->create();
