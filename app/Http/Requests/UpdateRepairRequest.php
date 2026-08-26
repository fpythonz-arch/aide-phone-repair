<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => 'sometimes|required|string|max:150',
            'client_phone' => 'sometimes|required|string|max:30',
            'client_email' => 'nullable|email|max:150',

            'device_brand' => 'sometimes|required|string|max:80',
            'device_model' => 'sometimes|required|string|max:120',
            'device_imei' => 'nullable|string|max:50',

            'problem_description' => 'sometimes|required|string|min:3|max:2000',
            'diagnosis' => 'nullable|string|max:2000',
            'technician' => 'nullable|string|max:100',

            'status' => 'nullable|in:new,received,diagnosing,waiting_quote,quote_accepted,in_progress,waiting_parts,testing,ready,delivered,cancelled',
            'priority' => 'nullable|in:low,normal,high,urgent',

            'cost_estimate' => 'nullable|numeric|min:0',
            'cost_final' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',

            'parts_used' => 'nullable|array',
            'parts_used.*' => 'string',
            'notes' => 'nullable|string|max:2000',

            'estimated_ready' => 'nullable|date',
            'warranty_days' => 'nullable|integer|min:0|max:365',
        ];
    }
}
