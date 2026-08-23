<?php

namespace App\Http\Middleware;

use App\Exceptions\MCPException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MCPAuthMiddleware
{
    /**
     * Clés API autorisées (en production, utiliser la base de données ou cache).
     */
    protected array $apiKeys = [];

    public function __construct()
    {
        $this->apiKeys = config('mcp.authorized_keys', []);
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Vérification de la clé API
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            throw MCPException::unauthorized(
                'Clé API manquante. Veuillez fournir un header X-API-Key.'
            );
        }

        if (!$this->isValidApiKey($apiKey)) {
            throw MCPException::unauthorized(
                'Clé API invalide ou révoquée.'
            );
        }

        // Vérification du rate limit pour MCP
        if ($this->isRateLimited($request)) {
            throw MCPException::invalidParams(
                'Trop de requêtes. Veuillez réessayer dans quelques instants.',
                ['retry_after' => $this->getRetryAfter($request)]
            );
        }

        // Enrichir la requête avec les infos du client MCP
        $request->attributes->set('mcp_client', $this->getClientInfo($apiKey));
        $request->attributes->set('mcp_request_id', $this->generateRequestId());

        return $next($request);
    }

    /**
     * Vérifie si la clé API est valide.
     */
    protected function isValidApiKey(string $apiKey): bool
    {
        // En production : vérifier en base de données ou cache Redis
        if (app()->environment('local', 'testing')) {
            return true; // Mode dev : tolérant
        }

        return in_array($apiKey, $this->apiKeys, true);
    }

    /**
     * Vérifie le rate limiting.
     */
    protected function isRateLimited(Request $request): bool
    {
        $clientId = $request->ip();
        $key = 'mcp_rate_limit:' . $clientId;
        $maxAttempts = (int) config('mcp.rate_limit.max_attempts', 60);
    $decayMinutes = (int) config('mcp.rate_limit.decay_minutes', 1);

        $attempts = cache()->get($key, 0);

        if ($attempts >= $maxAttempts) {
            return true;
        }

        cache()->put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return false;
    }

    /**
     * Retourne le temps d'attente avant réessai.
     */
    protected function getRetryAfter(Request $request): int
    {
        $key = 'mcp_rate_limit:' . $request->ip();
        $ttl = cache()->ttl($key);

        return $ttl > 0 ? $ttl : 60;
    }

    /**
     * Récupère les infos du client MCP.
     */
    protected function getClientInfo(string $apiKey): array
    {
        // En production : récupérer depuis la base de données
        return [
            'key_id' => substr($apiKey, 0, 8) . '...',
            'permissions' => ['read', 'diagnostic', 'component_read'],
            'rate_limit_tier' => 'standard',
        ];
    }

    /**
     * Génère un ID de requête unique.
     */
    protected function generateRequestId(): string
    {
        return uniqid('mcp_', true);
    }
}