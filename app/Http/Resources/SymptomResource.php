<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymptomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->translateCategory($this->category),
            'severity_level' => $this->severity_level,
            'severity_label' => $this->getSeverityLabel($this->severity_level),
            'common_devices' => $this->common_devices,
            'keywords' => $this->keywords,
            'components' => $this->whenLoaded('components', function () {
                return $this->components->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'probability' => $component->pivot->probability ?? null,
                    ];
                });
            }),
            'guides_count' => $this->whenCounted('repairGuides'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function translateCategory(string $category): string
    {
        return match ($category) {
            'display' => 'Affichage',
            'battery' => 'Batterie',
            'charging' => 'Charge',
            'audio' => 'Audio',
            'connectivity' => 'Connectivité',
            'performance' => 'Performance',
            'camera' => 'Caméra',
            'buttons' => 'Boutons',
            'water_damage' => 'Dommage eau',
            'software' => 'Logiciel',
            default => $category,
        };
    }

    protected function getSeverityLabel(int $level): string
    {
        return match ($level) {
            1 => 'Mineur',
            2 => 'Léger',
            3 => 'Modéré',
            4 => 'Sérieux',
            5 => 'Critique',
            default => 'Inconnu',
        };
    }
}