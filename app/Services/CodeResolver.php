<?php

namespace App\Services;

use App\Models\SecretCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CodeResolver
{
    public function resolve(string $input, ?string $brand = null, ?string $model = null): Collection
    {
        $query = SecretCode::query();

        $exactMatch = $query->clone()->where('code', $input)->first();

        if ($exactMatch) {
            return collect([$this->formatCode($exactMatch, 'exact')]);
        }

        $query->where(function ($q) use ($input) {
            $q->where('name', 'LIKE', "%{$input}%")
                ->orWhere('description', 'LIKE', "%{$input}%")
                ->orWhere('code', 'LIKE', "%{$input}%")
                ->orWhere('functionality', 'LIKE', "%{$input}%");
        });

        if ($brand) {
            $query->where(function ($q) use ($brand) {
                $q->whereJsonContains('compatible_brands', $brand)
                    ->orWhereNull('compatible_brands');
            });
        }

        if ($model) {
            $query->where(function ($q) use ($model) {
                $q->whereJsonContains('compatible_models', $model)
                    ->orWhereNull('compatible_models');
            });
        }

        return $query->orderByDesc('is_verified')
            ->orderByDesc('user_rating')
            ->get()
            ->map(fn ($code) => $this->formatCode($code, 'fuzzy'));
    }

    public function getByBrand(string $brand): Collection
    {
        $cacheKey = "codes:brand:{$brand}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($brand) {
            return SecretCode::whereJsonContains('compatible_brands', $brand)
                ->orWhereJsonContains('compatible_brands', 'Tous les modèles')
                ->orderBy('category')
                ->orderByDesc('is_verified')
                ->get()
                ->map(fn ($code) => $this->formatCode($code, 'brand'));
        });
    }

    public function getByCategory(string $category, ?string $brand = null): Collection
    {
        $query = SecretCode::where('category', $category);

        if ($brand) {
            $query->whereJsonContains('compatible_brands', $brand);
        }

        return $query->orderByDesc('is_verified')
            ->orderBy('name')
            ->get()
            ->map(fn ($code) => $this->formatCode($code, 'category'));
    }

    public function getCategories(): Collection
    {
        return SecretCode::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category,
                'label' => $this->translateCategory($item->category),
                'count' => $item->count,
            ]);
    }

    public function validateSafety(string $code): array
    {
        $secretCode = SecretCode::where('code', $code)->first();

        if (!$secretCode) {
            return [
                'safe' => false,
                'reason' => 'Code non répertorié dans notre base de données.',
                'risk_level' => 'unknown',
            ];
        }

        $risks = [];

        if (!$secretCode->is_verified) {
            $risks[] = 'Ce code n\'est pas vérifié. Utilisez-le à vos risques et périls.';
        }

        if ($secretCode->category === 'reset') {
            $risks[] = 'Ce code peut effacer des données. Sauvegardez avant utilisation.';
        }

        if (str_contains(strtolower($secretCode->name), 'reset') || str_contains(strtolower($secretCode->name), 'usine')) {
            $risks[] = 'Opération irréversible possible.';
        }

        $riskLevel = match (true) {
            $secretCode->category === 'reset' => 'high',
            !$secretCode->is_verified => 'medium',
            !empty($secretCode->warnings) => 'low',
            default => 'none',
        };

        $warnings = is_array($secretCode->warnings) 
            ? $secretCode->warnings 
            : (is_string($secretCode->warnings) ? json_decode($secretCode->warnings, true) ?? [] : []);

        return [
            'safe' => empty($risks) || $riskLevel === 'none',
            'code' => $secretCode->code,
            'name' => $secretCode->name,
            'risk_level' => $riskLevel,
            'warnings' => array_merge($risks, $warnings),
            'is_verified' => $secretCode->is_verified,
            'source' => $secretCode->source,
            'recommendation' => $this->getSafetyRecommendation($riskLevel),
        ];
    }

    public function getPopular(int $limit = 10): Collection
    {
        return SecretCode::where('is_verified', true)
            ->orderByDesc('user_rating')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn ($code) => $this->formatCode($code, 'popular'));
    }

    protected function formatCode(SecretCode $code, string $matchType): array
    {
        return [
            'id' => $code->id,
            'code' => $code->code,
            'name' => $code->name,
            'description' => $code->description,
            'functionality' => $code->functionality,
            'category' => $code->category,
            'category_label' => $this->translateCategory($code->category),
            'compatible_brands' => $code->compatible_brands,
            'compatible_models' => $code->compatible_models,
            'instructions' => $code->instructions,
            'warnings' => $code->warnings,
            'is_verified' => $code->is_verified,
            'source' => $code->source,
            'user_rating' => $code->user_rating,
            'match_type' => $matchType,
            'safety_status' => $this->validateSafety($code->code),
        ];
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

    protected function getSafetyRecommendation(string $riskLevel): string
    {
        return match ($riskLevel) {
            'high' => 'Sauvegardez absolument toutes vos données avant d\'utiliser ce code. Lisez attentivement les avertissements.',
            'medium' => 'Ce code semble fonctionner mais n\'est pas officiellement vérifié. Procédez avec prudence.',
            'low' => 'Code généralement sûr, mais lisez les avertissements.',
            'none' => 'Ce code est vérifié et sûr à utiliser.',
            default => 'Utilisez à vos risques et périls.',
        };
    }

    public function getStatistics(): array
    {
        return [
            'total' => SecretCode::count(),
            'verified' => SecretCode::where('is_verified', true)->count(),
            'by_category' => SecretCode::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_brand' => SecretCode::select('compatible_brands')
                ->get()
                ->flatMap(fn ($c) => $c->compatible_brands ?? [])
                ->countBy()
                ->sortDesc()
                ->take(10)
                ->toArray(),
            'average_rating' => round(SecretCode::whereNotNull('user_rating')->avg('user_rating'), 1),
        ];
    }
}