<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Espace « Mon atelier » : réservé au responsable (rôle Admin) de CET atelier.
 * Les membres et statistiques ne concernent jamais un autre atelier.
 */
class WorkshopController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $workshop = $request->user()->workshop;

        if ($workshop === null) {
            return response()->json(['success' => false, 'message' => 'Aucun atelier associé à ce compte.'], 404);
        }

        $members = $workshop->users()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'created_at'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $workshop->id,
                'name' => $workshop->name,
                'created_at' => $workshop->created_at,
                'repairs_count' => Repair::query()->count(), // déjà limité à l'atelier par le filtre global
                'members' => $members,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $workshop = $request->user()->workshop;

        if ($workshop === null) {
            return response()->json(['success' => false, 'message' => 'Aucun atelier associé à ce compte.'], 404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Le nom de l\'atelier est obligatoire.',
        ]);

        $workshop->update($data);

        return response()->json([
            'success' => true,
            'data' => ['id' => $workshop->id, 'name' => $workshop->name],
        ]);
    }
}
