<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Isolation par atelier : un utilisateur connecté ne voit QUE les lignes de son atelier.
 *
 * - Sans utilisateur (console, seeders, tests) : aucun filtre.
 * - Utilisateur sans atelier : aucune donnée (fermé par défaut, jamais ouvert).
 * - Les lignes d'un autre atelier se comportent comme inexistantes (404, pas 403) : on ne révèle rien.
 */
class WorkshopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('sanctum')->user();

        if ($user === null) {
            return;
        }

        if ($user->workshop_id === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('workshop_id'), $user->workshop_id);
    }
}
