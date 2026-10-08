<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Journal des requêtes.
 *
 * Confidentialité : on ne journalise JAMAIS le corps de la requête, les en-têtes
 * ni la query string (ils peuvent contenir nom, téléphone, e-mail, IMEI de clients
 * ou des secrets). Seules des métadonnées techniques sont écrites.
 */
class RequestLoggerMiddleware
{
    /**
     * Routes à exclure du logging.
     */
    protected array $excludedRoutes = [
        'api/health',
        'api/ping',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        if (! $this->shouldSkip($request)) {
            $this->logResponse($request, $response, $requestId, $startTime);
        }

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = $request->path();

        foreach ($this->excludedRoutes as $excluded) {
            if (str_contains($path, $excluded)) {
                return true;
            }
        }

        return false;
    }

    protected function logResponse(Request $request, Response $response, string $requestId, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms
        $status = $response->getStatusCode();

        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $status,
            'duration_ms' => $duration,
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip' => $request->ip(),
        ];

        if ($status >= 500) {
            Log::channel('requests')->error('Request failed', $context);
        } elseif ($status >= 400) {
            Log::channel('requests')->warning('Request error', $context);
        } else {
            Log::channel('requests')->info('Request completed', $context);
        }

        if ($duration > 5000) {
            Log::channel('requests')->warning('Slow request detected', $context + ['threshold_ms' => 5000]);
        }
    }
}
