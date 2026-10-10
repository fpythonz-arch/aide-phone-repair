<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;

/**
 * Espace « Administration de la plateforme ».
 *
 * Vue d'ensemble uniquement : comptes et volumes. Cet espace ne donne JAMAIS accès aux
 * réparations ni aux clients des ateliers (confidentialité) : seulement des compteurs.
 */
class PlatformController extends Controller
{
    public function overview(): JsonResponse
    {
        // withoutGlobalScopes : on compte sur TOUTE la plateforme, pas seulement l'atelier de l'administrateur.
        $repairCounts = Repair::query()
            ->withoutGlobalScopes()
            ->selectRaw('workshop_id, count(*) as total')
            ->groupBy('workshop_id')
            ->pluck('total', 'workshop_id');

        $workshops = Workshop::query()
            ->withCount('users')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'name' => $workshop->name,
                'created_at' => $workshop->created_at,
                'members_count' => $workshop->users_count,
                'repairs_count' => (int) ($repairCounts[$workshop->id] ?? 0),
            ])
            ->values();

        $recentUsers = User::query()
            ->with('workshop:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_platform_admin' => (bool) $user->is_platform_admin,
                'workshop' => $user->workshop?->name,
                'created_at' => $user->created_at,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'totals' => [
                    'workshops' => Workshop::query()->count(),
                    'users' => User::query()->count(),
                    'repairs' => Repair::query()->withoutGlobalScopes()->count(),
                ],
                'workshops' => $workshops,
                'recent_users' => $recentUsers,
            ],
        ]);
    }
}
