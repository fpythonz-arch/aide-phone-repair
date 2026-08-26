<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repair extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'number',
        'legacy_number',
        'legacy_id',
        'client_id',
        'device_id',
        'technician_id',
        'client_name',
        'client_phone',
        'client_email',
        'device_brand',
        'device_model',
        'device_imei',
        'problem_description',
        'diagnosis',
        'technician',
        'status',
        'priority',
        'cost_estimate',
        'cost_final',
        'currency',
        'parts_used',
        'notes',
        'estimated_ready',
        'warranty_days',
    ];

    protected $casts = [
        'parts_used' => 'array',
        'cost_estimate' => 'decimal:2',
        'cost_final' => 'decimal:2',
        'estimated_ready' => 'date',
        'warranty_days' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function technicianUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
