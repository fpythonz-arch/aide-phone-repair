<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvolutionEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'event_type_label' => $this->translateEventType($this->event_type),
            'description' => $this->description,
            'severity_before' => $this->severity_before,
            'severity_after' => $this->severity_after,
            'severity_change' => $this->severity_after - $this->severity_before,
            'severity_trend' => $this->getSeverityTrend($this->severity_before, $this->severity_after),
            'device_model' => $this->device_model,
            'device_brand' => $this->device_brand,
            'repair_attempted' => $this->repair_attempted,
            'repair_successful' => $this->repair_successful,
            'repair_status' => $this->getRepairStatus(),
            'time_elapsed_days' => $this->time_elapsed_days,
            'environmental_factors' => $this->environmental_factors,
            'user_notes' => $this->user_notes,
            'logged_by' => $this->logged_by,
            'symptom' => $this->whenLoaded('symptom', function () {
                return [
                    'id' => $this->symptom->id,
                    'name' => $this->symptom->name,
                    'category' => $this->symptom->category,
                ];
            }),
            'component' => $this->whenLoaded('component', function () {
                return [
                    'id' => $this->component->id,
                    'name' => $this->component->name,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function translateEventType(string $type): string
    {
        return match ($type) {
            'symptom_worsening' => 'Aggravation du symptôme',
            'symptom_improvement' => 'Amélioration du symptôme',
            'new_symptom_appeared' => 'Nouveau symptôme',
            'repair_attempt' => 'Tentative de réparation',
            'component_failure' => 'Panne de composant',
            'temporary_fix' => 'Réparation temporaire',
            'recurring_issue' => 'Problème récurrent',
            default => $type,
        };
    }

    protected function getSeverityTrend(int $before, int $after): string
    {
        $diff = $after - $before;

        return match (true) {
            $diff > 0 => 'worsened',
            $diff < 0 => 'improved',
            default => 'stable',
        };
    }

    protected function getRepairStatus(): ?string
    {
        if (!$this->repair_attempted) {
            return 'not_attempted';
        }

        return $this->repair_successful ? 'successful' : 'failed';
    }
}