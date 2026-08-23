<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du protocole MCP (Model Context Protocol)
    |--------------------------------------------------------------------------
    |
    | Paramètres pour le serveur MCP qui gère les communications
    | entre l'IA et les différents serveurs spécialisés.
    |
    */

    'version' => '2024-11-05',

    'protocol' => 'jsonrpc-2.0',

    /*
    |--------------------------------------------------------------------------
    | Serveurs MCP disponibles
    |--------------------------------------------------------------------------
    |
    | Liste des serveurs spécialisés enregistrés dans l'application.
    |
    */
    'servers' => [
        'diagnostic' => [
            'class' => \App\MCP\Servers\DiagnosticServer::class,
            'enabled' => true,
            'description' => 'Serveur de diagnostic des pannes téléphone',
        ],
        'component' => [
            'class' => \App\MCP\Servers\ComponentServer::class,
            'enabled' => true,
            'description' => 'Serveur de mapping des composants',
        ],
        'codesecret' => [
            'class' => \App\MCP\Servers\CodeSecretServer::class,
            'enabled' => true,
            'description' => 'Serveur de résolution des codes secrets',
        ],
        'evolution' => [
            'class' => \App\MCP\Servers\EvolutionServer::class,
            'enabled' => true,
            'description' => 'Serveur de suivi d\'évolution des pannes',
        ],
        'tool' => [
            'class' => \App\MCP\Servers\ToolServer::class,
            'enabled' => true,
            'description' => 'Serveur de recommandation d\'outils',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentification MCP
    |--------------------------------------------------------------------------
    |
    | Clés API autorisées pour accéder au protocole MCP.
    | En production, privilégiez une gestion en base de données.
    |
    */
    'auth' => [
        'enabled' => env('MCP_AUTH_ENABLED', true),
        'header_name' => 'X-API-Key',
    ],

    'authorized_keys' => explode(',', env('MCP_API_KEYS', '')),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting MCP
    |--------------------------------------------------------------------------
    |
    | Limitation du nombre de requêtes par client.
    |
    */
   'rate_limit' => [
    'max_attempts' => (int) env('MCP_RATE_LIMIT_MAX_ATTEMPTS', 60),
    'decay_minutes' => (int) env('MCP_RATE_LIMIT_DECAY_MINUTES', 1),
],
    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    |
    | Durées maximales d'exécution pour les opérations MCP.
    |
    */
    'timeouts' => [
        'default' => 30,      // secondes
        'diagnostic' => 45,   // diagnostic complexe
        'component_map' => 20,
        'code_resolve' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache MCP
    |--------------------------------------------------------------------------
    |
    | Durées de mise en cache des résultats MCP.
    |
    */
    'cache' => [
        'enabled' => env('MCP_CACHE_ENABLED', true),
        'ttl_minutes' => [
            'servers_list' => 60,
            'diagnostic_result' => 30,
            'component_map' => 45,
            'code_lookup' => 120,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging MCP
    |--------------------------------------------------------------------------
    |
    | Configuration du logging des interactions MCP.
    |
    */
    'logging' => [
        'enabled' => env('MCP_LOGGING_ENABLED', true),
        'channel' => 'mcp',
        'log_requests' => true,
        'log_responses' => true,
        'log_errors' => true,
    ],
];