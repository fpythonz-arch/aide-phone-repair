<?php

namespace App\MCP\Servers;

use App\MCP\Core\Engine;
use App\Models\Symptom;
use Illuminate\Support\Facades\Cache;

class DiagnosticServer
{
    private Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $action, array $params): array
    {
        return match ($action) {
            'analyze' => $this->analyze($params),
            'symptoms' => $this->getSymptoms($params),
            'categories' => $this->getCategories(),
            'history' => $this->getHistory($params),
            default => ['error' => 'Action non supportée']
        };
    }

    private function analyze(array $params): array
    {
        $cacheKey = 'diagnostic_' . md5(json_encode($params));

        return Cache::remember($cacheKey, 3600, function () use ($params) {
            return $this->engine->runDiagnostic($params);
        });
    }

    private function getSymptoms(array $params): array
    {
        $query = Symptom::query();

        if (isset($params['category'])) {
            $query->where('category', $params['category']);
        }
        if (isset($params['brand'])) {
            $query->whereJsonContains('compatible_brands', $params['brand']);
        }
        if (isset($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'LIKE', "%{$params['search']}%")
                  ->orWhere('keywords', 'LIKE', "%{$params['search']}%");
            });
        }

        return [
            'symptoms' => $query->paginate(50)->items(),
            'total' => $query->count(),
            'categories' => Symptom::select('category')->distinct()->pluck('category')
        ];
    }

    private function getCategories(): array
    {
        return [
            'categories' => [
                ['id' => 'display', 'name' => 'Écran & Affichage', 'icon' => 'monitor', 'count' => 420],
                ['id' => 'battery', 'name' => 'Batterie & Alimentation', 'icon' => 'battery', 'count' => 380],
                ['id' => 'network', 'name' => 'Réseau & Connectivité', 'icon' => 'wifi', 'count' => 290],
                ['id' => 'audio', 'name' => 'Audio & Son', 'icon' => 'volume', 'count' => 310],
                ['id' => 'camera', 'name' => 'Caméra & Photos', 'icon' => 'camera', 'count' => 260],
                ['id' => 'software', 'name' => 'Logiciel & OS', 'icon' => 'code', 'count' => 520],
                ['id' => 'charging', 'name' => 'Charge & Connectique', 'icon' => 'plug', 'count' => 340],
                ['id' => 'buttons', 'name' => 'Boutons & Capteurs', 'icon' => 'circle', 'count' => 280],
                ['id' => 'water', 'name' => 'Oxydation & Liquide', 'icon' => 'droplet', 'count' => 180],
                ['id' => 'performance', 'name' => 'Performance & Lenteur', 'icon' => 'zap', 'count' => 260]
            ]
        ];
    }

    private function getHistory(array $params): array
    {
        return [
            'history' => [],
            'stats' => [
                'total_diagnostics' => 0,
                'success_rate' => 0.89,
                'avg_time' => '28 min'
            ]
        ];
    }
}