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
        // Les clés vides sont ignorées : une variable MCP_API_KEYS absente ne doit jamais autoriser personne.
        $this->apiKeys = array_values(array_filter(
            (array) config('mcp.authorized_keys', []),
            fn ($key) => is_string($key) && $key !== ''
        ));
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
     * Aucune exception selon l'environnement : même en local ou en test, une clé doit être configurée.
     * Comparaison à temps constant pour éviter les attaques par mesure du temps de réponse.
     */
    protected function isValidApiKey(string $apiKey): bool
    {
        foreach ($this->apiKeys as $validKey) {
            if (hash_equals($validKey, $apiKey)) {
                return true;
            }
        }

        return false;
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
            // Empreinte non réversible : ne jamais exposer le début de la clé elle-même.
            'key_id' => substr(hash('sha256', $apiKey), 0, 8),
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