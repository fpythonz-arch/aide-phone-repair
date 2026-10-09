<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Workshop;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower($validated['email'])])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => new UserResource($user->loadMissing('workshop')),
            ],
        ]);
    }

    /**
     * Inscription libre : crée un atelier privé dont la personne est l'administratrice.
     * Le rôle, l'atelier et les droits de plateforme ne viennent JAMAIS de la requête.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $workshop = Workshop::query()->create(['name' => $data['workshop_name']]);

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // haché par le cast du modèle
            ]);

            $user->forceFill([
                'role' => Roles::ADMIN,
                'workshop_id' => $workshop->id,
                'is_platform_admin' => false,
            ])->save();

            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $user->createToken('spa')->plainTextToken,
                'user' => new UserResource($user->load('workshop')),
            ],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->loadMissing('workshop')),
        ]);
    }
}
