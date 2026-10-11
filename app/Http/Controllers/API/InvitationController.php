<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoinRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\WorkshopInvitation;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Côté invité : vérifier un lien d'invitation, puis l'accepter en créant son compte.
 * Routes publiques (avant connexion), protégées par un jeton secret et une limitation de débit.
 */
class InvitationController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $invitation = WorkshopInvitation::findByToken($token);

        $this->ensureUsable($invitation);

        return response()->json([
            'success' => true,
            'data' => [
                'workshop_name' => $invitation->workshop->name,
                'role' => $invitation->role,
                'email' => $invitation->email,
                'expires_at' => $invitation->expires_at,
            ],
        ]);
    }

    public function accept(JoinRequest $request, string $token): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($token, $data) {
            // Verrou : deux acceptations simultanées du même lien ne peuvent pas réussir toutes les deux.
            $invitation = WorkshopInvitation::query()
                ->where('token_hash', WorkshopInvitation::hashToken($token))
                ->lockForUpdate()
                ->first();

            $this->ensureUsable($invitation);

            if ($invitation->email !== null && strtolower($invitation->email) !== $data['email']) {
                throw ValidationException::withMessages([
                    'email' => 'Cette invitation est réservée à une autre adresse e-mail.',
                ]);
            }

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // haché par le cast du modèle
            ]);

            // Rôle et atelier viennent UNIQUEMENT de l'invitation, jamais de la requête.
            $user->forceFill([
                'role' => $invitation->role,
                'workshop_id' => $invitation->workshop_id,
                'is_platform_admin' => false,
            ])->save();

            $invitation->forceFill(['accepted_at' => now(), 'accepted_by' => $user->id])->save();

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

    private function ensureUsable(?WorkshopInvitation $invitation): void
    {
        if ($invitation === null) {
            $this->fail('Invitation introuvable.', 404);
        }

        $message = match ($invitation->status()) {
            WorkshopInvitation::STATUS_ACCEPTED => 'Cette invitation a déjà été utilisée.',
            WorkshopInvitation::STATUS_REVOKED => 'Cette invitation a été annulée.',
            WorkshopInvitation::STATUS_EXPIRED => 'Cette invitation a expiré.',
            default => null,
        };

        if ($message !== null) {
            $this->fail($message, 410);
        }
    }

    private function fail(string $message, int $status): never
    {
        throw new HttpResponseException(response()->json(['success' => false, 'message' => $message], $status));
    }
}
