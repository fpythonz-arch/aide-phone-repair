<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvolutionEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type'       => 'required|string|max:100',
            'description'      => 'required|string|min:3|max:2000',
            'device_id'        => 'nullable|string|max:100',
            'device_brand'     => 'nullable|string|max:100',
            'device_model'     => 'nullable|string|max:100',
            'symptom_id'       => 'nullable|integer|exists:symptoms,id',
            'component_id'     => 'nullable|integer|exists:components,id',
            'severity'         => 'nullable|string|in:low,medium,high,critical',
            'severity_before'  => 'nullable|integer|between:1,5',
            'severity_after'   => 'nullable|integer|between:1,5',
            'repair_attempted' => 'nullable|boolean',
            'repair_successful'=> 'nullable|boolean',
            'user_notes'       => 'nullable|string|max:1000',
            'logged_by'        => 'nullable|string|max:100',
            'technician'       => 'nullable|string|max:100',
            'cost'             => 'nullable|numeric|min:0',
            'status'           => 'nullable|string|in:completed,pending,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'event_type.required'  => 'Le type d\'événement est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'description.min'      => 'La description doit contenir au moins 3 caractères.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir severity string → niveaux numériques si absent
        if ($this->has('severity') && !$this->has('severity_before')) {
            $map = ['low' => 1, 'medium' => 2, 'high' => 4, 'critical' => 5];
            $level = $map[$this->severity] ?? 2;
            $this->merge(['severity_before' => $level, 'severity_after' => $level]);
        }

        // Valeurs par défaut obligatoires pour le modèle
        if (!$this->has('device_brand')) {
            $this->merge(['device_brand' => 'Non spécifié']);
        }
        if (!$this->has('device_model')) {
            $this->merge(['device_model' => $this->device_id ?? 'Non spécifié']);
        }
        if (!$this->has('repair_attempted')) {
            $this->merge(['repair_attempted' => false]);
        }
        if (!$this->has('severity_before')) {
            $this->merge(['severity_before' => 2, 'severity_after' => 2]);
        }
    }
}
