<?php

namespace App\Services;

use App\Models\Component;
use App\Models\Symptom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ComponentMapper
{
    /**
     * Seuil de probabilité minimum pour considérer un composant comme probable.
     */
    protected float $probabilityThreshold = 30.0;

    /**
     * Mappe les composants probables pour un ensemble de symptômes.
     */
    public function mapBySymptoms(array $symptomIds): Collection
    {
        $cacheKey = 'component_map:' . md5(implode(',', $symptomIds));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($symptomIds) {
            $symptoms = Symptom::with('components')->whereIn('id', $symptomIds)->get();

            $componentScores = collect();

            foreach ($symptoms as $symptom) {
                foreach ($symptom->components as $component) {
                    $probability = $component->pivot->probability ?? 50;
                    $componentId = $component->id;

                    if (!$componentScores->has($componentId)) {
                        $componentScores->put($componentId, [
                            'component' => $component,
                            'total_probability' => 0,
                            'match_count' => 0,
                            'symptoms' => collect(),
                        ]);
                    }

                    $current = $componentScores->get($componentId);
                    $current['total_probability'] += $probability;
                    $current['match_count']++;
                    $current['symptoms']->push([
                        'id' => $symptom->id,
                        'name' => $symptom->name,
                        'probability' => $probability,
                    ]);

                    $componentScores->put($componentId, $current);
                }
            }

            return $componentScores->map(function ($data) {
                $avgProbability = $data['match_count'] > 0
                    ? $data['total_probability'] / $data['match_count']
                    : 0;

                $multiplier = min($data['match_count'], 3) * 0.15;
                $finalProbability = min($avgProbability * (1 + $multiplier), 100);

                return [
                    'id' => $data['component']->id,
                    'name' => $data['component']->name,
                    'category' => $data['component']->category,
                    'slug' => $data['component']->slug,
                    'description' => $data['component']->description,
                    'probability' => round($finalProbability, 1),
                    'match_count' => $data['match_count'],
                    'replacement_difficulty' => $data['component']->replacement_difficulty,
                    'price_range' => $data['component']->price_range,
                    'availability' => $data['component']->availability,
                    'image_url' => $data['component']->image_url,
                    'matched_symptoms' => $data['symptoms']->unique('id')->values(),
                    'technical_specs' => $data['component']->technical_specs,
                    'common_failures' => $data['component']->common_failures,
                ];
            })
                ->filter(fn ($item) => $item['probability'] >= $this->probabilityThreshold)
                ->sortByDesc('probability')
                ->values();
        });
    }

    /**
     * Mappe les composants par catégorie de symptôme.
     */
    public function mapByCategory(string $category): Collection
    {
        return Component::whereHas('symptoms', function ($query) use ($category) {
            $query->where('category', $category);
        })
            ->with(['symptoms' => function ($query) use ($category) {
                $query->where('category', $category);
            }])
            ->get()
            ->map(function ($component) {
                return [
                    'id' => $component->id,
                    'name' => $component->name,
                    'slug' => $component->slug,
                    'category' => $component->category,
                    'description' => $component->description,
                    'replacement_difficulty' => $component->replacement_difficulty,
                    'price_range' => $component->price_range,
                    'image_url' => $component->image_url,
                    'symptom_count' => $component->symptoms->count(),
                ];
            })
            ->sortBy('replacement_difficulty')
            ->values();
    }

    /**
     * Trouve les composants compatibles avec un appareil.
     */
    public function findCompatible(string $deviceModel, ?string $category = null): Collection
    {
        $query = Component::whereJsonContains('compatible_devices', $deviceModel);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get()
            ->map(function ($component) use ($deviceModel) {
                return [
                    'id' => $component->id,
                    'name' => $component->name,
                    'slug' => $component->slug,
                    'category' => $component->category,
                    'price_range' => $component->price_range,
                    'availability' => $component->availability,
                    'replacement_difficulty' => $component->replacement_difficulty,
                    'is_compatible' => true,
                    'compatibility_notes' => $component->technical_specs['compatibility_notes'] ?? null,
                ];
            });
    }

    /**
     * Analyse la faisabilité du remplacement d'un composant.
     */
    public function analyzeFeasibility(int $componentId, array $userSkills = []): array
    {
        $component = Component::with('replacementParts')->findOrFail($componentId);

        $difficulty = $component->replacement_difficulty;
        $hasTools = in_array('soldering_station', $userSkills) || $difficulty < 4;
        $hasExperience = in_array('microsoldering', $userSkills) || $difficulty < 5;

        $feasibility = match (true) {
            $difficulty <= 2 => 'easy',
            $difficulty === 3 && $hasTools => 'moderate',
            $difficulty === 4 && $hasTools && $hasExperience => 'difficult',
            $difficulty === 5 && $hasExperience => 'expert_only',
            default => 'not_recommended',
        };

        $estimatedTime = match ($difficulty) {
            1 => '15-30 min',
            2 => '30-60 min',
            3 => '1-2 heures',
            4 => '2-4 heures',
            5 => '4+ heures / professionnel',
        };

        $risks = [];
        if ($difficulty >= 4) {
            $risks[] = 'Risque de dommage à la carte mère';
            $risks[] = 'Nécessite un microscope et une station de soudure';
        }
        if ($difficulty >= 3) {
            $risks[] = 'Perte de l\'étanchéité';
        }
        if ($component->category === 'battery') {
            $risks[] = 'Risque d\'incendie si la batterie est percée';
        }

        $parts = $component->replacementParts->map(function ($part) {
            return [
                'id' => $part->id,
                'name' => $part->name,
                'price' => $part->price,
                'quality_grade' => $part->quality_grade,
                'in_stock' => $part->stock_status === 'available',
            ];
        });

        return [
            'component' => [
                'id' => $component->id,
                'name' => $component->name,
                'difficulty' => $difficulty,
            ],
            'feasibility' => $feasibility,
            'estimated_time' => $estimatedTime,
            'risks' => $risks,
            'required_skills' => $this->getRequiredSkills($difficulty),
            'user_has_skills' => [
                'tools' => $hasTools,
                'experience' => $hasExperience,
            ],
            'recommended_parts' => $parts,
            'professional_cost_estimate' => $this->estimateProfessionalCost($component),
        ];
    }

    protected function getRequiredSkills(int $difficulty): array
    {
        $baseSkills = ['Tournevis de précision', 'Spudger', 'Pince à épiler'];

        return match ($difficulty) {
            1 => $baseSkills,
            2 => array_merge($baseSkills, ['Ventouse', 'Sèche-cheveux']),
            3 => array_merge($baseSkills, ['Ventouse', 'Station air chaud', 'Alcool isopropylique']),
            4 => array_merge($baseSkills, ['Station de soudure', 'Microscope', 'Flux soudure', 'Fil à souder']),
            5 => array_merge($baseSkills, ['Station de reballing', 'Microscope trinoculaire', 'Stencils BGA', 'Programmateur EEPROM']),
        };
    }

    protected function estimateProfessionalCost(Component $component): array
    {
        $partsCost = $component->price_range['max'] ?? 50;
        $laborMultiplier = match ($component->replacement_difficulty) {
            1 => 1.5,
            2 => 2.0,
            3 => 2.5,
            4 => 4.0,
            5 => 6.0,
        };

        $laborCost = $partsCost * ($laborMultiplier - 1);
        $totalMin = $partsCost + ($laborCost * 0.7);
        $totalMax = $partsCost + ($laborCost * 1.3);

        return [
            'parts_cost' => round($partsCost, 2),
            'labor_cost_estimate' => round($laborCost, 2),
            'total_estimate' => [
                'min' => round($totalMin, 2),
                'max' => round($totalMax, 2),
            ],
            'currency' => 'EUR',
        ];
    }

    public function findAlternatives(int $componentId)
{
    $component = Component::find($componentId);
    
    if (!$component) {
        return collect();
    }

    return Component::where('id', '!=', $componentId)
        ->where('category', $component->category)
        ->where(function ($query) use ($component) {
            $devices = $component->compatible_devices ?? [];
            foreach ($devices as $device) {
                $query->orWhereJsonContains('compatible_devices', $device);
            }
        })
        ->get();
}
}