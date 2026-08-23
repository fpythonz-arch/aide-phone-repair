<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'symptom_id',
        'component_id',
        'title',
        'description',
        'difficulty_level',
        'estimated_time_minutes',
        'required_tools',
        'required_parts',
        'steps',
        'warnings',
        'video_url',
        'image_urls',
        'success_rate',
        'view_count',
        'is_published',
    ];

    protected $casts = [
        'required_tools' => 'array',
        'required_parts' => 'array',
        'steps' => 'array',
        'warnings' => 'array',
        'image_urls' => 'array',
        'success_rate' => 'float',
        'estimated_time_minutes' => 'integer',
        'difficulty_level' => 'integer', // 1-5
        'is_published' => 'boolean',
    ];

    /**
     * Le symptôme lié à ce guide.
     */
    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }

    /**
     * Le composant lié à ce guide.
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * Les pièces de rechange recommandées.
     */
    public function replacementParts(): HasMany
    {
        return $this->hasMany(ReplacementPart::class);
    }

    /**
     * Scope pour les guides publiés.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope pour les guides par niveau de difficulté.
     */
    public function scopeByDifficulty($query, int $level)
    {
        return $query->where('difficulty_level', $level);
    }
}