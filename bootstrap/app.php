<?php

use App\Exceptions\MCPException;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\MCPAuthMiddleware;
use App\Http\Middleware\RequestLoggerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux (appliqués à toutes les requêtes).
        // CORS : géré par le middleware natif de Laravel, configuré dans config/cors.php.
        $middleware->append(RequestLoggerMiddleware::class);

        // Alias pour utilisation dans les routes
        $middleware->alias([
            'mcp.auth' => MCPAuthMiddleware::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // app/Exceptions/Handler.php n'est pas utilisé par Laravel 11+/12 : sans ce rendu,
        // une clé MCP absente ou invalide produisait une erreur 500 au lieu d'un 401.
        $exceptions->render(function (MCPException $e, Request $request) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                    'data' => $e->getErrorData(),
                ],
                'id' => $e->getRequestId(),
            ], $e->getStatusCode());
        });
    })
    ->create();