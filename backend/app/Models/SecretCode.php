<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecretCode extends Model
{
    use HasFactory;

    protected $table = 'secret_codes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'functionality',
        'compatible_brands',
        'compatible_models',
        'category',
        'instructions',
        'warnings',
        'is_verified',
        'source',
        'user_rating',
    ];

    protected $casts = [
        'compatible_brands' => 'array',
        'compatible_models' => 'array',
        'instructions' => 'array',
        'warnings' => 'array',
        'keywords' => 'array',  // MANQUE ?
        'is_verified' => 'boolean',
        'user_rating' => 'float',
        'tags' => 'array',
        'steps' => 'array',
    ];

    /**
     * Scope pour les codes vérifiés.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope pour filtrer par marque.
     */
    public function scopeForBrand($query, string $brand)
    {
        return $query->whereJsonContains('compatible_brands', $brand);
    }
}