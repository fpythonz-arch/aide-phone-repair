<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $instructions = is_array($this->instructions) ? $this->instructions : [];
        $warnings = is_array($this->warnings) ? $this->warnings : [];
        $brands = is_array($this->compatible_brands) ? $this->compatible_brands : [];
        $models = is_array($this->compatible_models) ? $this->compatible_models : [];

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'functionality' => $this->functionality,
            'category' => $this->category,
            'instructions' => $instructions,
            'instructions_count' => count($instructions),
            'warnings' => $warnings,
            'warnings_count' => count($warnings),
            'compatible_brands' => $brands,
            'compatible_models' => $models,
            'is_verified' => $this->is_verified,
            'source' => $this->source,
            'user_rating' => $this->user_rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    protected function translateFunctionality(string $functionality): string
    {
        return match ($functionality) {
            'diagnostic_test' => 'Test de diagnostic',
            'info_display' => 'Affichage d\'informations',
            'hidden_menu' => 'Menu caché',
            'factory_reset' => 'Réinitialisation usine',
            'calibration' => 'Calibration',
            default => $functionality,
        };
    }

    protected function translateCategory(string $category): string
    {
        return match ($category) {
            'diagnostic' => 'Diagnostic',
            'information' => 'Information',
            'hidden_menu' => 'Menu caché',
            'service_mode' => 'Mode service',
            'reset' => 'Réinitialisation',
            'hardware_test' => 'Test matériel',
            default => $category,
        };
    }

    protected function translateSource(?string $source): string
    {
        return match ($source) {
            'official' => 'Officiel',
            'community' => 'Communauté',
            'technician' => 'Technicien',
            'forum' => 'Forum',
            default => $source ?? 'Inconnu',
        };
    }

    protected function formatStars(?float $rating): ?string
    {
        if ($rating === null) return null;

        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;

        return str_repeat('★', (int) $full) . ($half ? '½' : '') . str_repeat('☆', (int) $empty);
    }
}