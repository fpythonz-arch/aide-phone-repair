<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'sub_category',
        'image_url',
        'datasheet_url',
        'price_range',
        'availability',
        'compatible_devices',
        'technical_specs',
        'common_failures',
        'testing_procedure',
        'replacement_difficulty',
    ];

    protected $casts = [
        'price_range' => 'array',
        'compatible_devices' => 'array',
        'technical_specs' => 'array',
        'common_failures' => 'array',
        'replacement_difficulty' => 'integer',
    ];

    public function repairGuides(): HasMany
    {
        return $this->hasMany(RepairGuide::class);
    }

    public function replacementParts(): HasMany
    {
        return $this->hasMany(ReplacementPart::class);
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'symptom_component')
            ->withPivot('probability', 'notes')
            ->withTimestamps();
    }
}