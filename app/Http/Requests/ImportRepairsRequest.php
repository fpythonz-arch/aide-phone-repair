<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRepairsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'repairs' => 'required|array|max:500',

            'repairs.*.id' => 'nullable|string|max:100',
            'repairs.*.number' => 'nullable|string|max:50',

            'repairs.*.client_name' => 'required|string|max:150',
            'repairs.*.client_phone' => 'required|string|max:30',
            'repairs.*.client_email' => 'nullable|email|max:150',

            'repairs.*.device_brand' => 'required|string|max:80',
            'repairs.*.device_model' => 'required|string|max:120',
            'repairs.*.device_imei' => 'nullable|string|max:50',

            'repairs.*.problem_description' => 'required|string|max:2000',
            'repairs.*.diagnosis' => 'nullable|string|max:2000',
            'repairs.*.technician' => 'nullable|string|max:100',

            'repairs.*.status' => 'nullable|string|max:30',
            'repairs.*.priority' => 'nullable|string|max:20',

            'repairs.*.cost_estimate' => 'nullable|numeric|min:0',
            'repairs.*.cost_final' => 'nullable|numeric|min:0',
            'repairs.*.currency' => 'nullable|string|max:10',

            'repairs.*.parts_used' => 'nullable|array',
            'repairs.*.notes' => 'nullable|string|max:2000',

            'repairs.*.estimated_ready' => 'nullable|string|max:30',
            'repairs.*.warranty_days' => 'nullable|integer|min:0|max:365',

            'repairs.*.created_at' => 'nullable|string|max:40',
            'repairs.*.updated_at' => 'nullable|string|max:40',
        ];
    }
}
