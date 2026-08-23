<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Liste toutes les marques uniques
     */
    public function brands(): JsonResponse
    {
        $brands = Device::select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return response()->json([
            'brands' => $brands,
        ]);
    }

    /**
     * Liste tous les appareils
     */
    public function index(): JsonResponse
    {
        $devices = Device::where('is_active', true)
            ->orderBy('brand')
            ->orderBy('release_year', 'desc')
            ->get();

        return response()->json([
            'devices' => $devices,
        ]);
    }

    /**
     * Détails d'un appareil par slug
     */
    public function show(string $slug): JsonResponse
    {
        $device = Device::where('slug', $slug)->first();

        if (!$device) {
            return response()->json(['error' => 'Appareil non trouvé'], 404);
        }

        return response()->json([
            'device' => $device,
        ]);
    }

    /**
     * Appareils par marque
     */
    public function byBrand(string $brand): JsonResponse
    {
        $devices = Device::where('brand', $brand)
            ->where('is_active', true)
            ->orderBy('release_year', 'desc')
            ->get();

        return response()->json([
            'brand' => $brand,
            'devices' => $devices,
        ]);
    }

    /**
     * Recherche d'appareils
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        $devices = Device::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            })
            ->get();

        return response()->json([
            'results' => $devices,
        ]);
    }
}