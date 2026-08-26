<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'client_id' => $this->client_id,
            'device_id' => $this->device_id,
            'technician_id' => $this->technician_id,
            'client_name' => $this->client_name,
            'client_phone' => $this->client_phone,
            'client_email' => $this->client_email,
            'device_brand' => $this->device_brand,
            'device_model' => $this->device_model,
            'device_imei' => $this->device_imei,
            'problem_description' => $this->problem_description,
            'diagnosis' => $this->diagnosis,
            'technician' => $this->technician,
            'status' => $this->status,
            'priority' => $this->priority,
            'cost_estimate' => $this->cost_estimate !== null ? (float) $this->cost_estimate : null,
            'cost_final' => $this->cost_final !== null ? (float) $this->cost_final : null,
            'currency' => $this->currency,
            'parts_used' => $this->parts_used ?? [],
            'notes' => $this->notes,
            'estimated_ready' => $this->estimated_ready?->toDateString(),
            'warranty_days' => $this->warranty_days,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
