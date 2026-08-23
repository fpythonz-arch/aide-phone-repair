<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Symptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'severity',        // 'low' | 'medium' | 'high' | 'critical'
        'common_causes',   // array de causes probables
        'estimated_repair_time', // en minutes
    ];

    protected $casts = [
        'common_devices' => 'array',
        'keywords' => 'array',
    ];

    /**
     * Les appareils associés à ce symptôme (relation many-to-many).
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'device_symptom')
            ->withTimestamps();
    }

    /**
     * Les composants associés à ce symptôme (relation many-to-many).
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'symptom_component')
            ->withPivot('probability', 'notes')
            ->withTimestamps();
    }

    /**
     * Les guides de réparation liés à ce symptôme.
     */
    public function repairGuides(): HasMany
    {
        return $this->hasMany(RepairGuide::class);
    }

    /**
     * Les événements d'évolution liés à ce symptôme.
     */
    public function evolutionEvents(): HasMany
    {
        return $this->hasMany(EvolutionEvent::class);
    }
}