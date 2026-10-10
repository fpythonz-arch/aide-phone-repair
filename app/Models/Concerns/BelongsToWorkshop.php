<?php

namespace App\Models\Concerns;

use App\Models\Scopes\WorkshopScope;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * À utiliser sur tout modèle dont les données sont privées à un atelier.
 * workshop_id n'est volontairement PAS assignable en masse : il est fixé ici, depuis l'utilisateur connecté.
 */
trait BelongsToWorkshop
{
    public static function bootBelongsToWorkshop(): void
    {
        static::addGlobalScope(new WorkshopScope());

        static::creating(function (Model $model) {
            if ($model->workshop_id === null) {
                $user = auth('sanctum')->user();

                if ($user !== null && $user->workshop_id !== null) {
                    $model->workshop_id = $user->workshop_id;
                }
            }
        });
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
