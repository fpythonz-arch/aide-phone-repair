<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Symptom;
use Illuminate\Http\JsonResponse;

class SymptomController extends Controller
{
    /**
     * Liste tous les symptômes
     */
    public function index(): JsonResponse
    {
        $symptoms = Symptom::orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $symptoms,
        ]);
    }

    /**
     * Symptômes par appareil - retourne tous les symptômes (pas de table pivot)
     */
    public function getByDevice(int $deviceId): JsonResponse
    {
        $symptoms = Symptom::orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $symptoms,
        ]);
    }
}