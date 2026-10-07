<?php

/*
| CORS : un seul système (middleware natif de Laravel).
| Origines autorisées = liste de base + CORS_ALLOWED_ORIGINS (séparées par des virgules) + FRONTEND_URL.
| L'authentification se fait par token Bearer (pas de cookie) : supports_credentials reste à false.
*/
$origins = array_merge(
    [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://aide-phone-repair-three.vercel.app',
    ],
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
    [(string) env('FRONTEND_URL', '')],
);

$origins = array_values(array_unique(array_filter(array_map(
    fn (string $origin) => rtrim(trim($origin), '/'),
    $origins
))));

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-API-Key', 'X-Session-ID', 'Accept', 'Origin'],
    'exposed_headers' => ['X-Request-ID'],
    'max_age' => 3600,
    'supports_credentials' => false,
];
