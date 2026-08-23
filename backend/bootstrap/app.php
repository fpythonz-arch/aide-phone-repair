<?php

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\MCPAuthMiddleware;
use App\Http\Middleware\RequestLoggerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux (appliqués à toutes les requêtes)
        $middleware->append(CorsMiddleware::class);
        $middleware->append(RequestLoggerMiddleware::class);

        // Alias pour utilisation dans les routes
        $middleware->alias([
            'mcp.auth' => MCPAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();