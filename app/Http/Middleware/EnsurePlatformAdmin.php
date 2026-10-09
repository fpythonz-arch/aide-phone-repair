<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réserve une route aux administrateurs de la PLATEFORME (et non d'un simple atelier).
 * Sert aux données communes à tous les ateliers (catalogue, événements d'évolution).
 * À placer APRÈS auth:sanctum.
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Authentification requise.'], 401);
        }

        if (! $user->is_platform_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : action réservée aux administrateurs de la plateforme.',
            ], 403);
        }

        return $next($request);
    }
}
