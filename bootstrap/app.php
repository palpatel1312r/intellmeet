<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware aliases here
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            // Add any other middleware aliases you need
        ]);

        // COMPLETELY REMOVE the ValidatePostSize middlewzare
        $middleware->remove(\Illuminate\Http\Middleware\ValidatePostSize::class);

        // Also remove any other size-limiting middlewares
        $middleware->remove(\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class);

        // Add your own that does nothing
        $middleware->prepend(\App\Http\Middleware\DisablePostSizeValidation::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
