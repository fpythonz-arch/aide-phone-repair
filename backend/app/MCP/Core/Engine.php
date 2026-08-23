<?php

namespace App\MCP\Core;

use App\Models\Symptom;
use App\Models\Component;
use App\Models\RepairGuide;
use App\Services\DiagnosticFlow;

class Engine
{
    private DiagnosticFlow $diagnosticFlow;
    private array $context = [];

    public function __construct(DiagnosticFlow $diagnosticFlow)
    {
        $this->diagnosticFlow = $diagnosticFlow;
    }

    public function runDiagnostic(array $input): array
    {
        $this->context = [
            'brand' => $input['brand'] ?? null,
            'model' => $input['model'] ?? null,
            'symptoms' => $input['symptoms'] ?? [],
            'step' => 1,
            'confidence' => 0.0
        ];

        $identifiedSymptoms = $this->identifySymptoms($input['symptoms']);
        $affectedComponents = $this->correlateComponents($identifiedSymptoms);
        $rootCauses = $this->analyzeRootCauses($affectedComponents);
        $repairGuide = $this->generateRepairGuide($rootCauses);
        $result = $this->validateAndScore($repairGuide);

        return [
            'diagnostic_id' => uniqid('diag_'),
            'timestamp' => now()->toIso8601String(),
            'device' => [
                'brand' => $this->context['brand'],
                'model' => $this->context['model']
            ],
            'steps' => [
                'symptoms_identified' => $identifiedSymptoms,
                'affected_components' => $affectedComponents,
                'root_causes' => $rootCauses,
                'repair_guide' => $repairGuide,
                'validation' => $result
            ],
            'confidence_score' => $result['confidence'],
            'estimated_time' => $repairGuide['estimated_time'] ?? '30-45 min',
            'difficulty' => $repairGuide['difficulty'] ?? 'intermediate',
            'tools_required' => $repairGuide['tools'] ?? []
        ];
    }

    private function identifySymptoms(array $symptoms): array
    {
        $identified = [];
        foreach ($symptoms as $symptom) {
            $match = Symptom::where('keywords', 'LIKE', "%{$symptom}%")
                ->orWhere('description', 'LIKE', "%{$symptom}%")
                ->first();

            if ($match) {
                $identified[] = [
                    'id' => $match->id,
                    'name' => $match->name,
                    'category' => $match->category,
                    'severity' => $match->severity,
                    'confidence' => $this->calculateMatchConfidence($symptom, $match)
                ];
            }
        }
        return $identified;
    }

    private function correlateComponents(array $symptoms): array
    {
        $components = [];
        foreach ($symptoms as $symptom) {
            $related = Component::whereHas('symptoms', function ($q) use ($symptom) {
                $q->where('symptom_id', $symptom['id']);
            })->get();

            foreach ($related as $component) {
                $components[] = [
                    'id' => $component->id,
                    'name' => $component->name,
                    'location' => $component->location,
                    'replaceable' => $component->replaceable,
                    'estimated_cost' => $component->estimated_cost,
                    'linked_symptom' => $symptom['name']
                ];
            }
        }
        return $components;
    }

    private function analyzeRootCauses(array $components): array
    {
        $causes = [];
        foreach ($components as $component) {
            $causes[] = [
                'component' => $component['name'],
                'probable_causes' => [
                    'Défaut matériel',
                    'Oxydation / liquide',
                    'Usure normale',
                    'Choc / chute',
                    'Surcharge électrique'
                ],
                'diagnostic_tests' => $this->getTestsForComponent($component['name']),
                'priority' => $this->calculatePriority($component)
            ];
        }
        return $causes;
    }

    private function generateRepairGuide(array $causes): array
    {
        $steps = [];
        $tools = [];
        $totalTime = 0;

        foreach ($causes as $cause) {
            $guide = RepairGuide::where('component', $cause['component'])->first();
            if ($guide) {
                $steps[] = [
                    'step' => count($steps) + 1,
                    'title' => $guide->title,
                    'instructions' => $guide->steps,
                    'warnings' => $guide->warnings,
                    'images' => $guide->image_urls,
                    'video_url' => $guide->video_url
                ];
                $tools = array_merge($tools, $guide->tools_required);
                $totalTime += $guide->estimated_minutes;
            }
        }

        return [
            'steps' => $steps,
            'tools' => array_unique($tools),
            'estimated_time' => $this->formatTime($totalTime),
            'difficulty' => $this->calculateDifficulty($steps),
            'safety_warnings' => [
                'Débrancher la batterie avant toute intervention',
                'Utiliser un tapis ESD',
                'Travailler dans un environnement propre et éclairé'
            ]
        ];
    }

    private function validateAndScore(array $guide): array
    {
        $confidence = min(0.95, 0.4 + (count($guide['steps']) * 0.15));

        return [
            'confidence' => round($confidence, 2),
            'validation_passed' => $confidence > 0.5,
            'recommendations' => $confidence < 0.7
                ? ['Considérer une expertise en magasin']
                : ['Diagnostic fiable - procéder à la réparation']
        ];
    }

    private function calculateMatchConfidence(string $input, $match): float
    {
        similar_text(strtolower($input), strtolower($match->name), $percent);
        return round($percent / 100, 2);
    }

    private function getTestsForComponent(string $componentName): array
    {
        $tests = [
            'Écran' => ['Test tactile', 'Test pixels', 'Test rétroéclairage'],
            'Batterie' => ['Test voltage', 'Test charge cycle', 'Test décharge'],
            'Caméra' => ['Test autofocus', 'Test capteur', 'Test flash'],
            'Connecteur de charge' => ['Test continuité', 'Test voltage', 'Test données'],
            'Haut-parleur' => ['Test fréquence', 'Test distorsion', 'Test volume']
        ];
        return $tests[$componentName] ?? ['Test visuel', 'Test fonctionnel'];
    }

    private function calculatePriority(array $component): string
    {
        return $component['replaceable'] ? 'high' : 'medium';
    }

    private function formatTime(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours > 0 ? "{$hours}h {$mins}min" : "{$mins} min";
    }

    private function calculateDifficulty(array $steps): string
    {
        $count = count($steps);
        if ($count <= 2) return 'easy';
        if ($count <= 5) return 'intermediate';
        return 'advanced';
    }
}