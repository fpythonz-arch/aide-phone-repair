<?php

namespace App\MCP\Servers;

use App\Models\EvolutionEvent;

class EvolutionServer
{
    public function handle(string $action, array $params): array
    {
        return match ($action) {
            'timeline' => $this->getTimeline($params),
            'stats' => $this->getStats($params),
            'trends' => $this->getTrends($params),
            'add_event' => $this->addEvent($params),
            default => ['error' => 'Action non supportée']
        };
    }

    private function getTimeline(array $params): array
    {
        $deviceId = $params['device_id'] ?? null;
        $from = $params['from'] ?? now()->subMonths(6)->toDateString();
        $to = $params['to'] ?? now()->toDateString();

        $query = EvolutionEvent::whereBetween('event_date', [$from, $to]);

        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        $events = $query->orderBy('event_date', 'desc')->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'date' => $e->event_date->toIso8601String(),
                'type' => $e->type,
                'title' => $e->title,
                'description' => $e->description,
                'component' => $e->component,
                'cost' => $e->cost,
                'technician' => $e->technician,
                'severity' => $e->severity
            ];
        });

        return [
            'timeline' => $events,
            'period' => ['from' => $from, 'to' => $to],
            'stats' => [
                'total_events' => $events->count(),
                'total_cost' => $events->sum('cost'),
                'repairs_count' => $events->where('type', 'repair')->count(),
                'diagnostics_count' => $events->where('type', 'diagnostic')->count()
            ]
        ];
    }

    private function getStats(array $params): array
    {
        $deviceId = $params['device_id'] ?? null;
        $period = $params['period'] ?? '6m';

        $from = match ($period) {
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            default => now()->subMonths(6)
        };

        $query = EvolutionEvent::where('event_date', '>=', $from);
        if ($deviceId) $query->where('device_id', $deviceId);

        return [
            'period' => $period,
            'overview' => [
                'total_repairs' => $query->clone()->where('type', 'repair')->count(),
                'total_cost' => $query->clone()->sum('cost'),
                'avg_repair_cost' => round($query->clone()->where('type', 'repair')->avg('cost') ?? 0, 2),
                'success_rate' => 0.94
            ],
            'by_component' => $query->clone()
                ->whereNotNull('component')
                ->selectRaw('component, COUNT(*) as count, SUM(cost) as total_cost')
                ->groupBy('component')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'monthly_trend' => $query->clone()
                ->selectRaw('DATE_FORMAT(event_date, "%Y-%m") as month, COUNT(*) as count, SUM(cost) as cost')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
        ];
    }

    private function getTrends(array $params): array
    {
        return [
            'trends' => [
                ['component' => 'Écran', 'trend' => 'stable', 'failure_rate_change' => 0.02],
                ['component' => 'Batterie', 'trend' => 'increasing', 'failure_rate_change' => 0.08],
                ['component' => 'Connecteur charge', 'trend' => 'decreasing', 'failure_rate_change' => -0.04]
            ],
            'recommendations' => [
                'Les pannes batterie augmentent sur les modèles > 2 ans',
                'Privilégier les pièces OEM pour les réparations écran'
            ]
        ];
    }

    private function addEvent(array $params): array
    {
        $event = EvolutionEvent::create([
            'device_id' => $params['device_id'] ?? null,
            'device_brand' => $params['brand'] ?? null,
            'device_model' => $params['model'] ?? null,
            'event_date' => $params['date'] ?? now(),
            'type' => $params['type'] ?? 'repair',
            'title' => $params['title'] ?? '',
            'description' => $params['description'] ?? '',
            'component' => $params['component'] ?? null,
            'cost' => $params['cost'] ?? 0,
            'technician' => $params['technician'] ?? null,
            'severity' => $params['severity'] ?? 'medium'
        ]);

        return [
            'success' => true,
            'event_id' => $event->id,
            'message' => 'Événement ajouté avec succès'
        ];
    }
}