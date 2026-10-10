<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Gestion des membres de SON atelier par le responsable (rôle Admin).
 * Un membre d'un autre atelier est introuvable (404).
 */
class WorkshopMemberController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(Roles::invitable())],
        ], [
            'role.in' => 'Ce rôle ne peut pas être attribué.',
        ]);

        $member = $this->findMember($request, $id);
        if ($member instanceof JsonResponse) {
            return $member;
        }

        $member->forceFill(['role' => $data['role']])->save();

        return response()->json([
            'success' => true,
            'data' => ['id' => $member->id, 'name' => $member->name, 'role' => $member->role],
        ]);
    }

    /** Retire un membre : ses connexions sont coupées et il n'a plus accès à aucun atelier. */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $member = $this->findMember($request, $id);
        if ($member instanceof JsonResponse) {
            return $member;
        }

        DB::transaction(function () use ($member) {
            $member->tokens()->delete();
            $member->forceFill(['workshop_id' => null, 'role' => Roles::TECHNICIAN])->save();
        });

        return response()->json(['success' => true]);
    }

    private function findMember(Request $request, int $id): User|JsonResponse
    {
        $workshop = $request->user()->workshop;

        $member = $workshop?->users()->whereKey($id)->first();

        if ($member === null) {
            return response()->json(['success' => false, 'message' => 'Membre introuvable.'], 404);
        }

        if ($member->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre compte ici.'], 422);
        }

        if ($member->role === Roles::ADMIN) {
            return response()->json(['success' => false, 'message' => 'Le responsable de l\'atelier ne peut pas être modifié.'], 422);
        }

        return $member;
    }
}
