<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosticResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        // Si c'est un objet stdClass (depuis getResult()), convertir
        if (is_array($data)) {
            $data = (object) $data;
        }

        return [
            'status' => $data->status ?? 'unknown',
            'device' => $data->device ?? null,
            'severity' => [
                'level' => $data->severity['level'] ?? 'unknown',
                'max' => $data->severity['max'] ?? 0,
                'average' => $data->severity['average'] ?? 0,
                'critical_count' => $data->severity['critical_count'] ?? 0,
                'label' => $this->getSeverityLabel($data->severity['level'] ?? 'unknown'),
                'color' => $this->getSeverityColor($data->severity['level'] ?? 'unknown'),
            ],
            'symptoms' => $this->formatSymptoms($data->symptoms ?? []),
            'components' => $this->formatComponents($data->components ?? []),
            'guides' => $this->formatGuides($data->guides ?? []),
            'validation' => $data->validation ?? null,
            'metadata' => [
                'started_at' => $data->metadata['started_at'] ?? null,
                'completed_at' => $data->metadata['completed_at'] ?? null,
                'total_steps' => $data->metadata['total_steps'] ?? 0,
                'current_step' => $data->metadata['current_step'] ?? 0,
                'progress_percent' => $this->calculateProgress(
                    $data->metadata['current_step'] ?? 0,
                    $data->metadata['total_steps'] ?? 0
                ),
            ],
            'summary' => $this->generateSummary($data),
        ];
    }

    protected function getSeverityLabel(string $level): string
    {
        return match ($level) {
            'critical' => 'Critique - Intervention immédiate requise',
            'high' => 'Élevée - Réparation recommandée rapidement',
            'medium' => 'Modérée - Réparation conseillée',
            'low' => 'Faible - Problème mineur',
            default => 'Inconnue',
        };
    }

    protected function getSeverityColor(string $level): string
    {
        return match ($level) {
            'critical' => '#dc2626',
            'high' => '#ea580c',
            'medium' => '#ca8a04',
            'low' => '#16a34a',
            default => '#6b7280',
        };
    }

    protected function formatSymptoms(array $symptoms): array
    {
        return array_map(function ($symptom) {
            if (is_object($symptom)) {
                $symptom = (array) $symptom;
            }
            return [
                'id' => $symptom['id'] ?? null,
                'name' => $symptom['name'] ?? 'Inconnu',
                'category' => $symptom['category'] ?? null,
                'severity_level' => $symptom['severity_level'] ?? null,
                'description' => $symptom['description'] ?? null,
            ];
        }, $symptoms);
    }

    protected function formatComponents(array $components): array
    {
        return array_map(function ($component) {
            if (is_object($component)) {
                $component = (array) $component;
            }
            return [
                'id' => $component['id'] ?? null,
                'name' => $component['name'] ?? 'Inconnu',
                'category' => $component['category'] ?? null,
                'probability' => $component['match_probability'] ?? ($component['probability'] ?? 0),
                'difficulty' => $component['replacement_difficulty'] ?? null,
                'price_range' => $component['price_range'] ?? null,
            ];
        }, $components);
    }

    protected function formatGuides(array $guides): array
    {
        return array_map(function ($guide) {
            if (is_object($guide)) {
                $guide = (array) $guide;
            }
            return [
                'id' => $guide['id'] ?? null,
                'title' => $guide['title'] ?? 'Sans titre',
                'difficulty_level' => $guide['difficulty_level'] ?? null,
                'success_rate' => $guide['success_rate'] ?? null,
                'estimated_time_minutes' => $guide['estimated_time_minutes'] ?? null,
            ];
        }, $guides);
    }

    protected function calculateProgress(int $current, int $total): int
    {
        if ($total === 0) return 0;
        return min(100, round(($current / $total) * 100));
    }

    protected function generateSummary($data): array
    {
        $symptoms = $data->symptoms ?? [];
        $components = $data->components ?? [];
        $guides = $data->guides ?? [];
        $severity = $data->severity ?? [];

        return [
            'total_symptoms_detected' => count($symptoms),
            'probable_components_count' => count($components),
            'recommended_guides_count' => count($guides),
            'severity_level' => $severity['level'] ?? 'unknown',
            'estimated_repair_time' => $this->estimateTotalRepairTime($guides),
            'estimated_cost_range' => $this->estimateCostRange($components),
            'can_self_repair' => $this->canSelfRepair($guides),
        ];
    }

    protected function estimateTotalRepairTime(array $guides): ?array
    {
        if (empty($guides)) return null;

        $times = array_filter(array_map(function ($g) {
            if (is_object($g)) $g = (array) $g;
            return $g['estimated_time_minutes'] ?? null;
        }, $guides));

        if (empty($times)) return null;

        return [
            'min_minutes' => min($times),
            'max_minutes' => max($times),
            'formatted' => $this->formatTimeRange(min($times), max($times)),
        ];
    }

    protected function formatTimeRange(int $min, int $max): string
    {
        if ($min === $max) {
            return $min . ' min';
        }
        return $min . ' - ' . $max . ' min';
    }

    protected function estimateCostRange(array $components): ?array
    {
        if (empty($components)) return null;

        $mins = [];
        $maxs = [];

        foreach ($components as $c) {
            if (is_object($c)) $c = (array) $c;
            if (!empty($c['price_range'])) {
                $mins[] = $c['price_range']['min'] ?? 0;
                $maxs[] = $c['price_range']['max'] ?? 0;
            }
        }

        if (empty($mins)) return null;

        return [
            'min' => min($mins),
            'max' => max($maxs),
            'currency' => 'EUR',
            'formatted' => min($mins) . '€ - ' . max($maxs) . '€',
        ];
    }

    protected function canSelfRepair(array $guides): bool
    {
        if (empty($guides)) return false;

        foreach ($guides as $guide) {
            if (is_object($guide)) $guide = (array) $guide;
            if (($guide['difficulty_level'] ?? 5) <= 3) {
                return true;
            }
        }

        return false;
    }
}