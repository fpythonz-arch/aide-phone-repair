<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\RepairGuide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // ← AJOUTER CECI

class ToolController extends Controller
{
    /**
     * Liste les outils recommandés pour une réparation.
     */
        public function forRepair(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guide_id' => 'nullable|integer|exists:repair_guides,id',
            'component_id' => 'nullable|integer|exists:components,id',
            'difficulty_level' => 'nullable|integer|between:1,5',
        ]);

        // Vérifie qu'au moins un paramètre est fourni
        if (empty($validated['guide_id']) && empty($validated['component_id']) && empty($validated['difficulty_level'])) {
            return response()->json([
                'message' => 'At least one parameter is required: guide_id, component_id, or difficulty_level',
                'errors' => [
                    'guide_id' => ['Please provide guide_id, component_id, or difficulty_level'],
                ],
            ], 422);
        }

        $tools = collect();

        if (!empty($validated['guide_id'])) {
            $guide = RepairGuide::findOrFail($validated['guide_id']);
            $tools = $tools->merge($guide->required_tools ?? []);
        }

        if (!empty($validated['component_id'])) {
            $component = Component::findOrFail($validated['component_id']);
            $tools = $tools->merge($this->suggestToolsForDifficulty($component->replacement_difficulty ?? 1));
        }

        if (!empty($validated['difficulty_level'])) {
            $tools = $tools->merge($this->suggestToolsForDifficulty((int) $validated['difficulty_level']));
        }

        $tools = $tools->unique()->values();

        return response()->json([
            'tools' => $tools,
            'estimated_cost' => $this->estimateToolsCost($tools),
            'where_to_buy' => $this->suggestSuppliers(),
        ]);
    }

    /**
     * Calcule le coût estimé des outils.
     */
    protected function estimateToolsCost(Collection $tools): array // ← Collection avec le bon namespace
    {
        $toolPrices = [
            'Tournevis cruciforme PH00' => 8,
            'Tournevis pentalobe P2' => 6,
            'Tournevis plat 1.5mm' => 5,
            'Spudger en plastique' => 3,
            'Pince à épiler' => 4,
            'Pince à épiler ESD' => 12,
            'Ventouse démontage écran' => 5,
            'Tweezers de précision' => 8,
            'Tweezers ESD' => 15,
            'Sèche-cheveux' => 0,
            'Station air chaud' => 45,
            'Alcool isopropylique 99%' => 8,
            'Carte plastique (type carte bancaire)' => 0,
            'Station de soudure' => 80,
            'Microscope' => 120,
            'Flux soudure' => 10,
            'Fil à souder' => 8,
            'Station de reballing' => 350,
            'Microscope trinoculaire' => 250,
            'Stencils BGA' => 25,
            'Programmateur EEPROM' => 60,
            'Multimètre' => 20,
        ];

        $total = 0;
        $breakdown = [];

        foreach ($tools as $tool) {
            $price = $toolPrices[$tool] ?? 10;
            $total += $price;
            $breakdown[] = [
                'tool' => $tool,
                'estimated_price' => $price > 0 ? round($price, 2) : 'Déjà possédé',
            ];
        }

        return [
            'total_estimate' => round($total, 2),
            'currency' => 'EUR',
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Suggère des fournisseurs d'outils.
     */
    protected function suggestSuppliers(): array
    {
        return [
            [
                'name' => 'iFixit',
                'url' => 'https://www.ifixit.com',
                'specialty' => 'Outils de réparation smartphone',
                'quality' => 'premium',
            ],
            [
                'name' => 'Amazon',
                'url' => 'https://www.amazon.fr',
                'specialty' => 'Large choix, livraison rapide',
                'quality' => 'variable',
            ],
            [
                'name' => 'AliExpress',
                'url' => 'https://fr.aliexpress.com',
                'specialty' => 'Prix bas, pièces chinoises',
                'quality' => 'budget',
            ],
            [
                'name' => 'Microsoldering Supply',
                'url' => 'https://microsolderingsupply.com',
                'specialty' => 'Équipement microsoudure',
                'quality' => 'professional',
            ],
        ];
    }

    /**
     * Suggère les outils selon le niveau de difficulté.
     */
    protected function suggestToolsForDifficulty(int $difficulty): array
    {
        $baseTools = [
            'Tournevis cruciforme PH00',
            'Spudger en plastique',
            'Pince à épiler',
        ];

        return match ($difficulty) {
            1 => $baseTools,
            2 => array_merge($baseTools, [
                'Tournevis pentalobe P2',
                'Ventouse démontage écran',
                'Carte plastique (type carte bancaire)',
            ]),
            3 => array_merge($baseTools, [
                'Tournevis pentalobe P2',
                'Ventouse démontage écran',
                'Sèche-cheveux',
                'Tweezers de précision',
                'Alcool isopropylique 99%',
            ]),
            4 => array_merge($baseTools, [
                'Tournevis pentalobe P2',
                'Station air chaud',
                'Tweezers ESD',
                'Station de soudure',
                'Flux soudure',
                'Fil à souder',
                'Multimètre',
            ]),
            5 => array_merge($baseTools, [
                'Station air chaud',
                'Tweezers ESD',
                'Station de soudure',
                'Microscope',
                'Station de reballing',
                'Microscope trinoculaire',
                'Stencils BGA',
                'Programmateur EEPROM',
                'Multimètre',
            ]),
        };
    }

    /**
     * Vérifie si l'utilisateur a les outils nécessaires.
     */
    public function checkInventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guide_id' => 'required|integer|exists:repair_guides,id',
            'owned_tools' => 'required|array',
            'owned_tools.*' => 'string',
        ]);

        $guide = RepairGuide::findOrFail($validated['guide_id']);
        $requiredTools = collect($guide->required_tools ?? []);
        $ownedTools = collect($validated['owned_tools']);

        $missing = $requiredTools->diff($ownedTools)->values();
        $has = $requiredTools->intersect($ownedTools)->values();

        $missingCost = $this->estimateToolsCost($missing);

        return response()->json([
            'guide_id' => $guide->id,
            'guide_title' => $guide->title,
            'tools_status' => [
                'required_total' => $requiredTools->count(),
                'owned' => $has->count(),
                'missing' => $missing->count(),
                'owned_list' => $has,
                'missing_list' => $missing,
            ],
            'missing_tools_cost' => $missingCost,
            'ready_to_repair' => $missing->isEmpty(),
        ]);
    }

    /**
     * Liste tous les outils de base recommandés pour un kit de démarrage.
     */
    public function starterKit(): JsonResponse
    {
        $kit = [
            'essential' => [
                'Tournevis cruciforme PH00',
                'Tournevis pentalobe P2',
                'Spudger en plastique',
                'Pince à épiler',
                'Ventouse démontage écran',
            ],
            'recommended' => [
                'Tweezers de précision',
                'Sèche-cheveux',
                'Alcool isopropylique 99%',
                'Carte plastique (type carte bancaire)',
            ],
            'advanced' => [
                'Station air chaud',
                'Tweezers ESD',
                'Station de soudure',
                'Multimètre',
            ],
        ];

        $costs = [
            'essential' => $this->estimateToolsCost(collect($kit['essential']))['total_estimate'],
            'recommended' => $this->estimateToolsCost(collect($kit['recommended']))['total_estimate'],
            'advanced' => $this->estimateToolsCost(collect($kit['advanced']))['total_estimate'],
        ];

        return response()->json([
            'kits' => $kit,
            'estimated_costs' => $costs,
            'suppliers' => $this->suggestSuppliers(),
        ]);
    }
}