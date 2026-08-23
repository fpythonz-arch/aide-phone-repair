<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Symptom;
use App\Models\Component;
use App\Models\RepairGuide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiagnosticController extends Controller
{
    /**
     * Initialise un nouveau diagnostic
     */
    public function initialize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand' => 'required|string',
            'model' => 'required|string',
            'imei' => 'nullable|string',
        ]);

        $sessionId = Str::uuid()->toString();

        return response()->json([
            'data' => [
                'session_id' => $sessionId,
                'device' => [
                    'brand' => $validated['brand'],
                    'model' => $validated['model'],
                ],
            ],
        ]);
    }

    /**
     * Analyse les symptômes sélectionnés
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symptoms' => 'required|array',
            'symptoms.*' => 'integer|exists:symptoms,id',
            'device_id' => 'nullable|integer',
            'session_id' => 'nullable|string',
        ]);

        $symptomIds = $validated['symptoms'];
        $symptoms = Symptom::whereIn('id', $symptomIds)->get();

        // Trouver les composants liés aux symptômes
        $components = Component::whereHas('symptoms', function ($query) use ($symptomIds) {
            $query->whereIn('symptom_id', $symptomIds);
        })->get();

        // Trouver les guides de réparation liés aux composants
        $repairGuides = RepairGuide::whereIn('component_id', $components->pluck('id'))
            ->orWhereHas('component', function ($query) use ($symptomIds) {
                $query->whereHas('symptoms', function ($q) use ($symptomIds) {
                    $q->whereIn('symptom_id', $symptomIds);
                });
            })
            ->get();

        // Déterminer la sévérité globale
        $severity = 'low';
        if ($symptoms->contains('severity', 'critical')) {
            $severity = 'critical';
        } elseif ($symptoms->contains('severity', 'high')) {
            $severity = 'high';
        } elseif ($symptoms->contains('severity', 'medium')) {
            $severity = 'medium';
        }

        // Générer les recommandations
        $recommendations = $this->generateRecommendations($symptoms, $components);

        // Calculer le temps estimé
        $estimatedTime = $repairGuides->sum('estimated_time');

        return response()->json([
            'data' => [
                'symptoms' => $symptoms,
                'components' => $components,
                'repair_guides' => $repairGuides,
                'severity' => $severity,
                'confidence' => 0.85,
                'recommendations' => $recommendations,
                'estimated_time' => $estimatedTime,
                'estimated_cost' => [
                    'min' => $components->count() * 25,
                    'max' => $components->count() * 85,
                    'currency' => 'EUR',
                ],
            ],
        ]);
    }

    /**
     * Valide les résultats du diagnostic
     */
    public function validateResults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'confirmed_symptoms' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        return response()->json([
            'data' => [
                'success' => true,
                'repair_plan' => [
                    'session_id' => $validated['session_id'],
                    'confirmed' => $validated['confirmed_symptoms'],
                ],
            ],
        ]);
    }

    /**
     * Prochaines étapes suggérées
     */
    public function nextSteps(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');

        return response()->json([
            'data' => [
                'steps' => [
                    'Vérifier la disponibilité des pièces',
                    'Préparer les outils nécessaires',
                    'Sauvegarder les données',
                ],
                'priority' => 'high',
            ],
        ]);
    }

    /**
     * Historique des diagnostics
     */
    public function history(): JsonResponse
    {
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * Génère des recommandations basées sur les symptômes et composants
     */
    private function generateRecommendations($symptoms, $components): array
    {
        $recommendations = [];

        if ($symptoms->contains('category', 'water')) {
            $recommendations[] = 'Ne pas allumer l\'appareil — risque de court-circuit';
            $recommendations[] = 'Démonter et nettoyer à l\'alcool isopropylique';
        }

        if ($symptoms->contains('category', 'battery') || $symptoms->contains('category', 'overheating')) {
            $recommendations[] = 'Remplacer la batterie par une pièce originale ou certifiée';
        }

        if ($symptoms->contains('category', 'screen')) {
            $recommendations[] = 'Vérifier si la vitre tactile ou la dalle est endommagée';
        }

        if ($components->count() > 3) {
            $recommendations[] = 'Diagnostic complexe — envisager une révision complète';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Procéder au remplacement des composants identifiés';
            $recommendations[] = 'Tester l\'appareil après chaque intervention';
        }

        return $recommendations;
    }
}