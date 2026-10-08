<?php

namespace App\Http\Middleware;

use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à certains rôles : ->middleware('role:Technicien senior').
 * Le rôle Admin passe toujours. À placer APRÈS auth:sanctum.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        if ($user->role === Roles::ADMIN || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé : droits insuffisants pour cette action.',
        ], 403);
    }
}
