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
        $middleware->trustProxies(at: '*');

        // ✅ Solution définitive — exclure toutes les routes POST/PUT/DELETE du CSRF
        // Inertia gère le CSRF via X-XSRF-TOKEN header, pas via _token dans le body
        $middleware->validateCsrfTokens(except: [
            '/auth/logout',
            '/langue/*',
            '/admin/*',
            '/profile*',
            '/publications/*',
            '/workflow/*',
            '/datasets/*',
            '/notifications/*',
            '/newsletter/*',
            '/contact',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => \App\Modules\Auth\Middleware\KeycloakMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
