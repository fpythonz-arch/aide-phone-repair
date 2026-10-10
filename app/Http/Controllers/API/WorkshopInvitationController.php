<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkshopInvitation;
use App\Rules\UniqueEmail;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Invitations de techniciens : réservé au responsable (rôle Admin) de l'atelier concerné.
 * Toutes les requêtes sont filtrées explicitement par l'atelier de l'utilisateur connecté.
 */
class WorkshopInvitationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invitations = WorkshopInvitation::query()
            ->where('workshop_id', $request->user()->workshop_id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invitations->map(fn (WorkshopInvitation $invitation) => $this->present($invitation))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $workshopId = $request->user()->workshop_id;

        if ($workshopId === null) {
            return response()->json(['success' => false, 'message' => 'Aucun atelier associé à ce compte.'], 404);
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $data = $request->validate([
            // Jamais « Admin » : une invitation ne peut donner que des rôles de technicien.
            'role' => ['required', Rule::in(Roles::invitable())],
            'email' => ['nullable', 'email:rfc', 'max:255', new UniqueEmail()],
        ], [
            'role.required' => 'Choisissez un rôle.',
            'role.in' => 'Ce rôle ne peut pas être attribué par invitation.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
        ]);

        $pending = WorkshopInvitation::query()
            ->where('workshop_id', $workshopId)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->count();

        if ($pending >= WorkshopInvitation::MAX_PENDING_PER_WORKSHOP) {
            return response()->json([
                'success' => false,
                'message' => 'Trop d\'invitations en attente. Annulez-en avant d\'en créer de nouvelles.',
            ], 422);
        }

        [$token, $hash] = WorkshopInvitation::newToken();

        $invitation = new WorkshopInvitation();
        $invitation->forceFill([
            'workshop_id' => $workshopId,
            'token_hash' => $hash,
            'role' => $data['role'],
            'email' => $data['email'] ?? null,
            'created_by' => $request->user()->id,
            'expires_at' => now()->addDays(WorkshopInvitation::TTL_DAYS),
        ])->save();

        return response()->json([
            'success' => true,
            // Le jeton n'est renvoyé QU'ICI, une seule fois : seule son empreinte est conservée.
            'data' => $this->present($invitation) + ['token' => $token],
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $invitation = WorkshopInvitation::query()
            ->where('workshop_id', $request->user()->workshop_id)
            ->whereKey($id)
            ->first();

        if ($invitation === null) {
            return response()->json(['success' => false, 'message' => 'Invitation introuvable.'], 404);
        }

        if (! $invitation->isUsable()) {
            return response()->json([
                'success' => false,
                'message' => 'Seule une invitation en attente peut être annulée.',
            ], 422);
        }

        $invitation->forceFill(['revoked_at' => now()])->save();

        return response()->json(['success' => true, 'data' => $this->present($invitation)]);
    }

    private function present(WorkshopInvitation $invitation): array
    {
        return [
            'id' => $invitation->id,
            'role' => $invitation->role,
            'email' => $invitation->email,
            'status' => $invitation->status(),
            'expires_at' => $invitation->expires_at,
            'accepted_at' => $invitation->accepted_at,
            'created_at' => $invitation->created_at,
        ];
    }
}
