<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerMiddleware
{
    /**
     * Routes à exclure du logging.
     */
    protected array $excludedRoutes = [
        'api/health',
        'api/ping',
    ];

    /**
     * Headers sensibles à masquer.
     */
    protected array $sensitiveHeaders = [
        'authorization',
        'x-api-key',
        'cookie',
        'x-csrf-token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_', true);

        // Attacher l'ID de requête
        $request->attributes->set('request_id', $requestId);

        // Logger la requête entrante
        if (!$this->shouldSkip($request)) {
            $this->logRequest($request, $requestId);
        }

        $response = $next($request);

        // Logger la réponse
        if (!$this->shouldSkip($request)) {
            $this->logResponse($request, $response, $requestId, $startTime);
        }

        // Ajouter l'ID de requête à la réponse
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Détermine si la requête doit être ignorée.
     */
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

    /**
     * Log la requête entrante.
     */
    protected function logRequest(Request $request, string $requestId): void
    {
        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ];

        // Log du body pour les requêtes POST/PUT/PATCH (limité)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $body = $request->all();
            $context['body'] = $this->sanitizeBody($body);
        }

        Log::channel('requests')->info('Incoming request', $context);
    }

    /**
     * Log la réponse.
     */
    protected function logResponse(Request $request, Response $response, string $requestId, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

        $context = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        // Log conditionnel selon le statut
        if ($response->getStatusCode() >= 500) {
            Log::channel('requests')->error('Request failed', $context);
        } elseif ($response->getStatusCode() >= 400) {
            Log::channel('requests')->warning('Request error', $context);
        } else {
            Log::channel('requests')->info('Request completed', $context);
        }

        // Alertes performance
        if ($duration > 5000) {
            Log::channel('requests')->warning('Slow request detected', array_merge($context, [
                'threshold_ms' => 5000,
            ]));
        }
    }

    /**
     * Masque les headers sensibles.
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $name => $values) {
            $lowerName = strtolower($name);

            if (in_array($lowerName, $this->sensitiveHeaders)) {
                $sanitized[$name] = ['***REDACTED***'];
            } else {
                $sanitized[$name] = $values;
            }
        }

        return $sanitized;
    }

    /**
     * Masque les champs sensibles du body.
     */
    protected function sanitizeBody(array $body): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'cvv',
            'pin',
        ];

        array_walk_recursive($body, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '***REDACTED***';
            }
        });

        return $body;
    }
}