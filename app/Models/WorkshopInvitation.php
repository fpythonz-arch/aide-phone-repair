<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Invitation à rejoindre un atelier.
 *
 * Volontairement SANS le filtre global par atelier : la vérification et l'acceptation d'un lien
 * se font avant toute connexion. Les routes du responsable filtrent explicitement par atelier.
 */
class WorkshopInvitation extends Model
{
    public const TTL_DAYS = 7;
    public const MAX_PENDING_PER_WORKSHOP = 20;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_EXPIRED = 'expired';

    /** workshop_id, role, token_hash... ne sont jamais assignables en masse : tout est fixé par le code. */
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** Génère un jeton aléatoire (affiché une seule fois) et son empreinte. */
    public static function newToken(): array
    {
        $token = Str::random(48);

        return [$token, self::hashToken($token)];
    }

    public static function findByToken(string $token): ?self
    {
        return self::query()->where('token_hash', self::hashToken($token))->first();
    }

    public function status(): string
    {
        if ($this->accepted_at !== null) {
            return self::STATUS_ACCEPTED;
        }
        if ($this->revoked_at !== null) {
            return self::STATUS_REVOKED;
        }
        if ($this->expires_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_PENDING;
    }

    public function isUsable(): bool
    {
        return $this->status() === self::STATUS_PENDING;
    }
}
