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
     * Symptômes par appareil (via table pivot)
     */
    public function getByDevice(int $deviceId): JsonResponse
    {
        $symptoms = Symptom::whereHas('devices', function ($query) use ($deviceId) {
            $query->where('device_id', $deviceId);
        })->get();

        return response()->json([
            'data' => $symptoms,
        ]);
    }
}