<?php

namespace App\MCP\Protocol;

use App\MCP\Core\Engine;

class MCPHandler
{
    public function __construct(
        protected Engine $engine
    ) {}

    /**
     * Traite une requête MCP.
     */
    public function process(string $method, array $params = [], ?string $server = null): array
    {
        // Dispatcher vers le bon serveur
        // TODO: Implémenter la logique réelle
        return [
            'method' => $method,
            'params' => $params,
            'server' => $server,
            'status' => 'processed',
        ];
    }
}