<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplacementPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'repair_guide_id',
        'name',
        'sku',
        'description',
        'price',
        'currency',
        'supplier',
        'supplier_url',
        'quality_grade',
        'warranty_months',
        'stock_status',
        'image_url',
        'compatibility_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'warranty_months' => 'integer',
        'compatibility_notes' => 'array',
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function repairGuide(): BelongsTo
    {
        return $this->belongsTo(RepairGuide::class);
    }
}