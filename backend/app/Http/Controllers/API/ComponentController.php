<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponentResource;
use App\Models\Component;
use App\Services\ComponentMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // ← AJOUTER SI MANQUANT

class ComponentController extends Controller
{
    public function __construct(
        protected ComponentMapper $componentMapper
    ) {}

    /**
     * Liste tous les composants avec filtres optionnels.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Component::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereJsonContains('common_failures', $search);
            });
        }

        if ($request->has('difficulty_max')) {
            $query->where('replacement_difficulty', '<=', $request->difficulty_max);
        }

        if ($request->has('device')) {
            $query->whereJsonContains('compatible_devices', $request->device);
        }

        $components = $query->orderBy('name')->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => ComponentResource::collection($components),
            'meta' => [
                'total' => $components->total(),
                'per_page' => $components->perPage(),
                'current_page' => $components->currentPage(),
                'last_page' => $components->lastPage(),
            ],
        ]);
    }

    /**
     * Affiche un composant spécifique.
     */
  public function show($id)
{
    try {
        $component = Component::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $component,
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'RESOURCE_NOT_FOUND',
                'message' => 'Component not found',
            ],
        ], 404);
    }
}
    /**
     * Mappe les composants probables à partir de symptômes.
     */
    public function mapBySymptoms(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symptom_ids' => 'required|array',
            'symptom_ids.*' => 'integer|exists:symptoms,id',
        ]);

        $components = $this->componentMapper->mapBySymptoms($validated['symptom_ids']);

        return response()->json([
            'data' => $components,
            'symptom_count' => count($validated['symptom_ids']),
            'component_count' => $components->count(),
        ]);
    }

    /**
     * Liste les composants par catégorie de symptôme.
     */
    public function byCategory(string $category): JsonResponse
    {
        $components = $this->componentMapper->mapByCategory($category);

        return response()->json([
            'category' => $category,
            'data' => $components,
        ]);
    }

    /**
     * Trouve les composants compatibles avec un appareil.
     */
    public function compatible(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_model' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
        ]);

        $components = $this->componentMapper->findCompatible(
            $validated['device_model'],
            $validated['category'] ?? null
        );

        return response()->json([
            'device_model' => $validated['device_model'],
            'data' => $components,
        ]);
    }

    /**
     * Analyse la faisabilité du remplacement d'un composant.
     */
    public function feasibility(Request $request, int $componentId): JsonResponse
    {
        $validated = $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => 'string',
        ]);

        $analysis = $this->componentMapper->analyzeFeasibility(
            $componentId,
            $validated['skills'] ?? []
        );

        return response()->json([
            'data' => $analysis,
        ]);
    }

    /**
     * Trouve des alternatives pour un composant.
     */
    public function alternatives(int $componentId): JsonResponse
    {
        $alternatives = $this->componentMapper->findAlternatives($componentId);

        return response()->json([
            'component_id' => $componentId,
            'alternatives_count' => $alternatives->count(),
            'data' => $alternatives,
        ]);
    }

    /**
     * Liste les catégories de composants disponibles.
     */
    public function categories(): JsonResponse
    {
        $categories = Component::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'categories' => $categories,
        ]);
    }
}