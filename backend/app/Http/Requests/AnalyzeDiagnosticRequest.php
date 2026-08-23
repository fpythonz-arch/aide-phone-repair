<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeDiagnosticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => 'nullable|string|max:100',
            'device' => 'nullable|array',
            'device.brand' => 'required_with:device|string|max:100',
            'device.model' => 'required_with:device|string|max:100',
            'device.imei' => 'nullable|string|size:15',
            'device.os_version' => 'nullable|string|max:50',
            'symptoms' => 'required|array|min:1',
            'symptoms.*' => 'string|min:2|max:255',
            'severity_notes' => 'nullable|array',
            'severity_notes.*' => 'integer|between:1,5',
        ];
    }

    public function messages(): array
    {
        return [
            'symptoms.required' => 'Veuillez décrire au moins un symptôme.',
            'symptoms.min' => 'Veuillez fournir au moins un symptôme.',
            'symptoms.*.min' => 'Chaque symptôme doit contenir au moins 2 caractères.',
            'device.brand.required_with' => 'La marque de l\'appareil est requise.',
            'device.model.required_with' => 'Le modèle de l\'appareil est requis.',
        ];
    }

    public function attributes(): array
    {
        return [
            'symptoms' => 'symptômes',
            'device.brand' => 'marque',
            'device.model' => 'modèle',
            'device.imei' => 'IMEI',
        ];
    }
}