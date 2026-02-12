<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1/motorcycle',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
        
        // Enable session and CSRF for API routes (needed for web admin panel)
        $middleware->statefulApi();
        
        // Encrypt cookies for API
        $middleware->encryptCookies(except: []);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
