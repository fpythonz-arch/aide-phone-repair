<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvolutionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'symptom_id',
        'component_id',
        'event_type',
        'description',
        'severity_before',
        'severity_after',
        'device_model',
        'device_brand',
        'repair_attempted',
        'repair_successful',
        'time_elapsed_days',
        'environmental_factors',
        'user_notes',
        'logged_by',
    ];

    protected $casts = [
        'repair_attempted' => 'boolean',
        'repair_successful' => 'boolean',
        'time_elapsed_days' => 'integer',
        'environmental_factors' => 'array',
        'severity_before' => 'integer',
        'severity_after' => 'integer',
    ];

    /**
     * Le symptôme lié à cet événement.
     */
    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }

    /**
     * Le composant lié à cet événement (optionnel).
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * Scope pour les événements de réparation réussie.
     */
    public function scopeSuccessfulRepairs($query)
    {
        return $query->where('repair_attempted', true)
            ->where('repair_successful', true);
    }

    /**
     * Scope pour les événements récents.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}