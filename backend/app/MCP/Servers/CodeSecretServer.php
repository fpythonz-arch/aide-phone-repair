<?php

namespace App\MCP\Servers;

use App\Models\SecretCode;

class CodeSecretServer
{
    public function handle(string $action, array $params): array
    {
        return match ($action) {
            'list' => $this->listCodes($params),
            'resolve' => $this->resolveCode($params),
            'brands' => $this->getBrands(),
            'test' => $this->testCode($params),
            default => ['error' => 'Action non supportée']
        };
    }

    private function listCodes(array $params): array
    {
        $query = SecretCode::query();

        if (isset($params['brand'])) {
            $query->where('brand', $params['brand']);
        }
        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }

        $codes = $query->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'description' => $c->description,
                'brand' => $c->brand,
                'type' => $c->type,
                'category' => $c->category,
                'risk_level' => $c->risk_level,
                'model_specific' => $c->model_specific
            ];
        });

        return ['codes' => $codes];
    }

    private function resolveCode(array $params): array
    {
        $brand = $params['brand'] ?? '';
        $purpose = $params['purpose'] ?? '';

        $codes = SecretCode::where('brand', $brand)
            ->where(function ($q) use ($purpose) {
                $q->where('name', 'LIKE', "%{$purpose}%")
                  ->orWhere('description', 'LIKE', "%{$purpose}%")
                  ->orWhere('category', $purpose);
            })
            ->get();

        return [
            'brand' => $brand,
            'purpose' => $purpose,
            'codes_found' => $codes->count(),
            'codes' => $codes->map(fn($c) => [
                'code' => $c->code,
                'name' => $c->name,
                'description' => $c->description,
                'risk' => $c->risk_level
            ])
        ];
    }

    private function getBrands(): array
    {
        return [
            'brands' => [
                ['name' => 'Samsung', 'codes_count' => 156, 'logo' => 'samsung'],
                ['name' => 'Apple', 'codes_count' => 89, 'logo' => 'apple'],
                ['name' => 'Xiaomi', 'codes_count' => 134, 'logo' => 'xiaomi'],
                ['name' => 'Huawei', 'codes_count' => 112, 'logo' => 'huawei'],
                ['name' => 'OnePlus', 'codes_count' => 78, 'logo' => 'oneplus'],
                ['name' => 'Google', 'codes_count' => 67, 'logo' => 'google'],
                ['name' => 'Sony', 'codes_count' => 95, 'logo' => 'sony'],
                ['name' => 'LG', 'codes_count' => 88, 'logo' => 'lg'],
                ['name' => 'Motorola', 'codes_count' => 72, 'logo' => 'motorola'],
                ['name' => 'Nokia', 'codes_count' => 64, 'logo' => 'nokia']
            ]
        ];
    }

    private function testCode(array $params): array
    {
        $code = $params['code'] ?? '';
        $brand = $params['brand'] ?? '';

        $isValidFormat = preg_match('/^(\*|#)[0-9*#]+(#)?$/', $code);

        $knownCode = SecretCode::where('code', $code)
            ->where('brand', $brand)
            ->first();

        return [
            'code' => $code,
            'valid_format' => (bool) $isValidFormat,
            'known_code' => $knownCode ? true : false,
            'code_info' => $knownCode ? [
                'name' => $knownCode->name,
                'description' => $knownCode->description,
                'risk' => $knownCode->risk_level,
                'warning' => $knownCode->risk_level === 'high'
                    ? '⚠️ Ce code peut effacer des données'
                    : null
            ] : null,
            'safety_check' => [
                'backup_recommended' => $knownCode && $knownCode->risk_level === 'high',
                'battery_level_ok' => true,
                'network_required' => str_contains($code, '*#')
            ]
        ];
    }
}