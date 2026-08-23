<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'slug',
        'release_year',
        'factory',
        'inventor',
        'specifications',
        'models_range',
        'history',
        'type',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'specifications' => 'array',
        'models_range' => 'array',
        'release_year' => 'integer',
        'is_active' => 'boolean',
    ];
}