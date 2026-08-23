<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCodeRequest;
use App\Http\Resources\SecretCodeResource;
use App\Models\SecretCode;
use App\Services\CodeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // ← AJOUTER SI MANQUANT

class CodeController extends Controller
{
    public function __construct(
        protected CodeResolver $codeResolver
    ) {}

    /**
     * Liste tous les codes secrets avec filtres.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SecretCode::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('brand')) {
            $query->whereJsonContains('compatible_brands', $request->brand);
        }

        if ($request->has('verified')) {
            $query->where('is_verified', filter_var($request->verified, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $codes = $query->orderByDesc('is_verified')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => SecretCodeResource::collection($codes),
            'meta' => [
                'total' => $codes->total(),
                'per_page' => $codes->perPage(),
                'current_page' => $codes->currentPage(),
            ],
        ]);
    }

    /**
     * Affiche un code secret spécifique.
     */
    public function show(SecretCode $code): JsonResponse
    {
        return response()->json([
            'data' => new SecretCodeResource($code),
        ]);
    }

    /**
     * Recherche et résout un code secret.
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => 'required|string|min:2|max:255',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:100',
        ]);

        $codes = $this->codeResolver->resolve(
            $validated['input'],
            $validated['brand'] ?? null,
            $validated['model'] ?? null
        );

        return response()->json([
            'query' => $validated['input'],
            'results_count' => $codes->count(),
            'data' => $codes,
        ]);
    }

    /**
     * Retourne les codes pour une marque spécifique.
     */
    public function byBrand(string $brand): JsonResponse
    {
        $codes = $this->codeResolver->getByBrand($brand);

        return response()->json([
            'brand' => $brand,
            'count' => $codes->count(),
            'data' => $codes,
        ]);
    }

    /**
     * Retourne les codes par catégorie.
     */
    public function byCategory(string $category, Request $request): JsonResponse
    {
        $brand = $request->query('brand');

        $codes = $this->codeResolver->getByCategory($category, $brand);

        return response()->json([
            'category' => $category,
            'brand_filter' => $brand,
            'count' => $codes->count(),
            'data' => $codes,
        ]);
    }

    /**
     * Valide la sécurité d'un code.
     */
    public function validateSafety(ValidateCodeRequest $request): JsonResponse
    {
        $safety = $this->codeResolver->validateSafety($request->code);

        return response()->json([
            'data' => $safety,
        ]);
    }

    /**
     * Retourne les codes les plus populaires.
     */
   public function popular(Request $request): JsonResponse
{
    $limit = (int) min($request->input('limit', 10), 50);

    $codes = $this->codeResolver->getPopular($limit);

    return response()->json([
        'limit' => $limit,
        'data' => $codes,
    ]);
}
    /**
     * Liste les catégories disponibles.
     */
    public function categories(): JsonResponse
    {
        $categories = $this->codeResolver->getCategories();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Statistiques des codes secrets.
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->codeResolver->getStatistics();

        return response()->json([
            'data' => $stats,
        ]);
    }
}