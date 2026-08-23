<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplacementPartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'price_formatted' => $this->formatPrice($this->price, $this->currency),
            'currency' => $this->currency,
            'supplier' => $this->supplier,
            'supplier_url' => $this->supplier_url,
            'quality_grade' => $this->quality_grade,
            'quality_label' => $this->getQualityLabel($this->quality_grade),
            'warranty_months' => $this->warranty_months,
            'warranty_label' => $this->formatWarranty($this->warranty_months),
            'stock_status' => $this->stock_status,
            'in_stock' => $this->stock_status === 'available',
            'image_url' => $this->image_url,
            'compatibility_notes' => $this->compatibility_notes,
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

    protected function formatPrice(?float $price, ?string $currency): ?string
    {
        if ($price === null) return null;

        $symbol = match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => $currency,
        };

        return number_format($price, 2) . ' ' . $symbol;
    }

    protected function getQualityLabel(?string $grade): string
    {
        return match ($grade) {
            'OEM' => 'Originale constructeur',
            'OEM_compatible' => 'Compatible OEM',
            'premium' => 'Premium',
            'standard' => 'Standard',
            'budget' => 'Économique',
            default => $grade ?? 'Non spécifié',
        };
    }

    protected function formatWarranty(?int $months): ?string
    {
        if ($months === null) return null;

        if ($months >= 12) {
            $years = floor($months / 12);
            $remaining = $months % 12;
            if ($remaining > 0) {
                return "{$years} an" . ($years > 1 ? 's' : '') . " et {$remaining} mois";
            }
            return "{$years} an" . ($years > 1 ? 's' : '');
        }

        return "{$months} mois";
    }
}