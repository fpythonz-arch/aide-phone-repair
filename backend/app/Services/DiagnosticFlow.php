<?php

namespace App\Services;

use App\Models\Symptom;
use App\Models\Component;
use App\Models\RepairGuide;
use App\Models\ReplacementPart;
use App\Exceptions\DiagnosticException;

class DiagnosticFlow
{
    /**
     * Analyze symptoms INDIVIDUALLY and return per-symptom results
     */
    public function analyze(array $symptomIds): array
    {
        if (empty($symptomIds)) {
            throw new DiagnosticException('Aucun symptôme fourni pour l\'analyse');
        }

        $symptoms = Symptom::whereIn('id', $symptomIds)
            ->with(['components', 'repairGuides', 'repairGuides.replacementParts'])
            ->get();

        if ($symptoms->isEmpty()) {
            throw new DiagnosticException('Aucun symptôme trouvé');
        }

        // ✅ ANALYSE INDIVIDUELLE PAR SYMPTÔME
        $perSymptomAnalysis = [];
        $allComponents = collect();
        $allRepairGuides = collect();
        $allReplacementParts = collect();
        $maxSeverity = 0;

        foreach ($symptoms as $symptom) {
            $symptomComponents = $symptom->components;
            $symptomGuides = $symptom->repairGuides;
            
            // Collecte pour le résumé global
            $allComponents = $allComponents->merge($symptomComponents);
            $allRepairGuides = $allRepairGuides->merge($symptomGuides);
            
            foreach ($symptomGuides as $guide) {
                $allReplacementParts = $allReplacementParts->merge($guide->replacementParts ?? collect());
            }

            $severity = $this->calculateSingleSeverity($symptom);
            if ($severity['score'] > $maxSeverity) {
                $maxSeverity = $severity['score'];
            }

            $perSymptomAnalysis[] = [
                'symptom_id' => $symptom->id,
                'symptom_name' => $symptom->name,
                'symptom_description' => $symptom->description,
                'category' => $symptom->category,
                'severity' => $severity,
                'components' => $symptomComponents->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'probability' => $component->pivot->probability ?? 'high',
                        'notes' => $component->pivot->notes ?? null,
                    ];
                })->toArray(),
                'repair_guides' => $symptomGuides->map(function ($guide) {
                    return [
                        'id' => $guide->id,
                        'title' => $guide->title,
                        'steps' => $guide->steps ?? [],
                        'difficulty' => $guide->difficulty ?? 'medium',
                        'estimated_time' => $guide->estimated_time ?? 30,
                        'tools_needed' => $guide->tools_needed ?? [],
                    ];
                })->toArray(),
                'replacement_parts' => $symptomGuides->flatMap(function ($guide) {
                    return $guide->replacementParts ?? collect();
                })->map(function ($part) {
                    return [
                        'id' => $part->id,
                        'name' => $part->name,
                        'reference' => $part->reference,
                        'price_estimate' => $part->price_estimate,
                        'availability' => $part->availability ?? 'in_stock',
                    ];
                })->toArray(),
            ];
        }

        // Résumé global
        $globalSeverity = $this->getGlobalSeverity($maxSeverity);

        return [
            'per_symptom' => $perSymptomAnalysis,
            'summary' => [
                'total_symptoms' => $symptoms->count(),
                'categories_affected' => $symptoms->pluck('category')->unique()->values()->toArray(),
                'global_severity' => $globalSeverity,
                'affected_components' => $allComponents->unique('id')->values()->toArray(),
                'all_repair_guides' => $allRepairGuides->unique('id')->values()->toArray(),
                'all_replacement_parts' => $allReplacementParts->unique('id')->values()->toArray(),
                'confidence_score' => $this->calculateConfidence($symptoms, $allComponents),
                'estimated_total_repair_time' => $allRepairGuides->sum('estimated_time') ?? 30,
            ],
        ];
    }

    /**
     * Calculate severity for a SINGLE symptom
     */
    protected function calculateSingleSeverity(Symptom $symptom): array
    {
        $severityMap = [
            1 => ['level' => 'low', 'label' => 'Faible - Problème mineur', 'color' => 'green'],
            2 => ['level' => 'low', 'label' => 'Faible - Surveillance recommandée', 'color' => 'green'],
            3 => ['level' => 'medium', 'label' => 'Moyen - Réparation conseillée', 'color' => 'yellow'],
            4 => ['level' => 'high', 'label' => 'Élevé - Réparation recommandée rapidement', 'color' => 'orange'],
            5 => ['level' => 'critical', 'label' => 'Critique - Attention immédiate requise', 'color' => 'red'],
        ];

        $score = $symptom->severity_level ?? 1;
        $severity = $severityMap[$score] ?? $severityMap[1];

        return [
            'score' => $score,
            'level' => $severity['level'],
            'label' => $severity['label'],
            'color' => $severity['color'],
        ];
    }

    /**
     * Get global severity based on max individual severity
     */
    protected function getGlobalSeverity(int $maxScore): array
    {
        $severityMap = [
            1 => ['level' => 'low', 'label' => 'Faible - Aucune urgence'],
            2 => ['level' => 'low', 'label' => 'Faible - Aucune urgence'],
            3 => ['level' => 'medium', 'label' => 'Moyen - Planifier une réparation'],
            4 => ['level' => 'high', 'label' => 'Élevé - Réparation recommandée sous 48h'],
            5 => ['level' => 'critical', 'label' => 'Critique - Risque de dommages irréversibles'],
        ];

        $severity = $severityMap[$maxScore] ?? $severityMap[1];

        return [
            'score' => $maxScore,
            'level' => $severity['level'],
            'label' => $severity['label'],
        ];
    }

    /**
     * Calculate confidence score
     */
    protected function calculateConfidence($symptoms, $components): float
    {
        if ($symptoms->isEmpty()) {
            return 0.0;
        }

        $hasComponents = $components->isNotEmpty();
        $symptomCount = $symptoms->count();
        
        $baseConfidence = min(0.3 + ($symptomCount * 0.15), 0.8);
        
        if ($hasComponents) {
            $baseConfidence += 0.15;
        }

        return round(min($baseConfidence, 1.0), 2);
    }
}