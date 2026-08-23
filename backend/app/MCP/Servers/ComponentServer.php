<?php

namespace App\MCP\Servers;

use App\Models\Component;

class ComponentServer
{
    public function handle(string $action, array $params): array
    {
        return match ($action) {
            'list' => $this->listComponents($params),
            'detail' => $this->getDetail($params),
            'diagram' => $this->getDiagram($params),
            'compatibility' => $this->checkCompatibility($params),
            default => ['error' => 'Action non supportée']
        };
    }

    private function listComponents(array $params): array
    {
        $query = Component::query();

        if (isset($params['brand'])) {
            $query->whereJsonContains('compatible_brands', $params['brand']);
        }
        if (isset($params['category'])) {
            $query->where('category', $params['category']);
        }

        $components = $query->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'category' => $c->category,
                'location' => $c->location,
                'replaceable' => $c->replaceable,
                'difficulty' => $c->difficulty,
                'estimated_cost' => $c->estimated_cost,
                'average_repair_time' => $c->average_repair_time,
                'image_url' => $c->image_url
            ];
        });

        return ['components' => $components];
    }

    private function getDetail(array $params): array
    {
        $component = Component::findOrFail($params['component_id'] ?? 0);

        return [
            'component' => [
                'id' => $component->id,
                'name' => $component->name,
                'description' => $component->description,
                'technical_specs' => $component->technical_specs,
                'common_failures' => $component->common_failures,
                'repair_guides' => $component->repairGuides,
                'compatible_models' => $component->compatible_models,
                'replacement_parts' => $component->replacementParts,
                'tools_required' => $component->tools_required,
                'warnings' => $component->warnings
            ]
        ];
    }

    private function getDiagram(array $params): array
    {
        $brand = $params['brand'] ?? 'generic';
        $model = $params['model'] ?? 'generic';

        return [
            'diagram' => [
                'brand' => $brand,
                'model' => $model,
                'layers' => [
                    'front' => $this->getFrontLayer(),
                    'internal' => $this->getInternalLayer(),
                    'back' => $this->getBackLayer()
                ],
                'hotspots' => $this->generateHotspots($brand, $model)
            ]
        ];
    }

    private function getFrontLayer(): array
    {
        return [
            ['id' => 'screen', 'name' => 'Écran', 'x' => 50, 'y' => 45, 'width' => 80, 'height' => 70],
            ['id' => 'earpiece', 'name' => 'Haut-parleur', 'x' => 50, 'y' => 15, 'width' => 20, 'height' => 5],
            ['id' => 'front_camera', 'name' => 'Caméra frontale', 'x' => 50, 'y' => 8, 'width' => 8, 'height' => 8],
            ['id' => 'proximity', 'name' => 'Capteur proximité', 'x' => 38, 'y' => 8, 'width' => 6, 'height' => 6],
            ['id' => 'home_button', 'name' => 'Bouton home', 'x' => 50, 'y' => 88, 'width' => 12, 'height' => 8]
        ];
    }

    private function getInternalLayer(): array
    {
        return [
            ['id' => 'motherboard', 'name' => 'Carte mère', 'x' => 50, 'y' => 50, 'width' => 70, 'height' => 60],
            ['id' => 'battery', 'name' => 'Batterie', 'x' => 50, 'y' => 75, 'width' => 60, 'height' => 30],
            ['id' => 'rear_camera', 'name' => 'Caméra arrière', 'x' => 30, 'y' => 25, 'width' => 15, 'height' => 15],
            ['id' => 'vibrator', 'name' => 'Vibreur', 'x' => 70, 'y' => 30, 'width' => 10, 'height' => 10],
            ['id' => 'charging_port', 'name' => 'Connecteur charge', 'x' => 50, 'y' => 95, 'width' => 20, 'height' => 5]
        ];
    }

    private function getBackLayer(): array
    {
        return [
            ['id' => 'back_cover', 'name' => 'Coque arrière', 'x' => 50, 'y' => 50, 'width' => 85, 'height' => 85],
            ['id' => 'flash', 'name' => 'Flash LED', 'x' => 35, 'y' => 22, 'width' => 6, 'height' => 6],
            ['id' => 'fingerprint', 'name' => 'Capteur empreinte', 'x' => 50, 'y' => 70, 'width' => 12, 'height' => 12],
            ['id' => 'nfc', 'name' => 'Antenne NFC', 'x' => 50, 'y' => 40, 'width' => 50, 'height' => 5]
        ];
    }

    private function generateHotspots(string $brand, string $model): array
    {
        return [
            ['component_id' => 'screen', 'failure_rate' => 0.35, 'avg_repair_cost' => 89.99],
            ['component_id' => 'battery', 'failure_rate' => 0.28, 'avg_repair_cost' => 49.99],
            ['component_id' => 'charging_port', 'failure_rate' => 0.22, 'avg_repair_cost' => 39.99],
            ['component_id' => 'rear_camera', 'failure_rate' => 0.15, 'avg_repair_cost' => 69.99],
            ['component_id' => 'motherboard', 'failure_rate' => 0.08, 'avg_repair_cost' => 199.99]
        ];
    }

    private function checkCompatibility(array $params): array
    {
        $component = Component::find($params['component_id'] ?? 0);
        $targetBrand = $params['target_brand'] ?? '';
        $targetModel = $params['target_model'] ?? '';

        $isCompatible = $component &&
            in_array($targetBrand, $component->compatible_brands ?? []);

        return [
            'compatible' => $isCompatible,
            'component' => $component?->name,
            'target' => "{$targetBrand} {$targetModel}",
            'alternatives' => $isCompatible ? [] : $this->findAlternatives($targetBrand, $component?->category)
        ];
    }

    private function findAlternatives(string $brand, ?string $category): array
    {
        if (!$category) return [];

        return Component::where('category', $category)
            ->whereJsonContains('compatible_brands', $brand)
            ->limit(5)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'cost' => $c->estimated_cost])
            ->toArray();
    }
}