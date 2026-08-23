<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Origines autorisées (configurables via .env).
     */
    protected array $allowedOrigins = [
        'http://localhost:5173',   // Vite dev server
        'http://localhost:3000',   // Alternative dev server
        'http://127.0.0.1:5173',
    ];

    /**
     * Méthodes HTTP autorisées.
     */
    protected array $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    /**
     * Headers autorisés.
     */
    protected array $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-API-Key',
        'X-Session-ID',
        'Accept',
        'Origin',
    ];

    /**
     * Headers exposés au client.
     */
    protected array $exposedHeaders = [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-Request-ID',
    ];

    /**
     * Durée de mise en cache des préflight (en secondes).
     */
    protected int $maxAge = 86400; // 24 heures

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        // Réponse aux requêtes preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
            return $this->addCorsHeaders($response, $origin);
        }

        $response = $next($request);

        return $this->addCorsHeaders($response, $origin);
    }

    /**
     * Ajoute les headers CORS à la réponse.
     */
    protected function addCorsHeaders(Response $response, ?string $origin): Response
    {
        // Origine autorisée ?
        if ($this->isOriginAllowed($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
        $response->headers->set('Access-Control-Max-Age', (string) $this->maxAge);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }

    /**
     * Vérifie si l'origine est autorisée.
     */
    protected function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        // Autoriser les origines dynamiques depuis la config
        $configuredOrigins = config('cors.allowed_origins', $this->allowedOrigins);

        foreach ($configuredOrigins as $allowed) {
            if ($allowed === '*') {
                return true;
            }

            if ($allowed === $origin) {
                return true;
            }

            // Support des wildcards (ex: https://*.example.com)
            if (str_contains($allowed, '*')) {
                $pattern = str_replace('\*', '.*', preg_quote($allowed, '/'));
                if (preg_match('/^' . $pattern . '$/', $origin)) {
                    return true;
                }
            }
        }

        return false;
    }
}