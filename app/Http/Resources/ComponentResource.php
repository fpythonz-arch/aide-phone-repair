<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'sub_category' => $this->sub_category,
            'image_url' => $this->image_url,
            'datasheet_url' => $this->datasheet_url,
            'price_range' => $this->price_range,
            'availability' => $this->availability,
            'availability_label' => $this->translateAvailability($this->availability),
            'compatible_devices' => $this->compatible_devices,
            'technical_specs' => $this->technical_specs,
            'common_failures' => $this->common_failures,
            'testing_procedure' => $this->testing_procedure,
            'replacement_difficulty' => $this->replacement_difficulty,
            'difficulty_label' => $this->getDifficultyLabel($this->replacement_difficulty),
            'symptoms' => $this->whenLoaded('symptoms', function () {
                return $this->symptoms->map(function ($symptom) {
                    return [
                        'id' => $symptom->id,
                        'name' => $symptom->name,
                        'probability' => $symptom->pivot->probability ?? null,
                    ];
                });
            }),
            'replacement_parts' => $this->whenLoaded('replacementParts', function () {
                return ReplacementPartResource::collection($this->replacementParts);
            }),
            'repair_guides' => $this->whenLoaded('repairGuides', function () {
                return RepairGuideResource::collection($this->repairGuides);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function translateAvailability(?string $availability): string
    {
        return match ($availability) {
            'in_stock' => 'En stock',
            'low_stock' => 'Stock limité',
            'out_of_stock' => 'Rupture de stock',
            'special_order' => 'Sur commande',
            default => $availability ?? 'Inconnu',
        };
    }

    protected function getDifficultyLabel(int $level): string
    {
        return match ($level) {
            1 => 'Très facile',
            2 => 'Facile',
            3 => 'Modéré',
            4 => 'Difficile',
            5 => 'Expert',
            default => 'Inconnu',
        };
    }
}