<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|min:3|max:50',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Veuillez saisir un code secret à valider.',
            'code.min' => 'Le code doit contenir au moins 3 caractères.',
            'code.max' => 'Le code ne doit pas dépasser 50 caractères.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'code secret',
            'brand' => 'marque',
            'model' => 'modèle',
        ];
    }

    /**
     * Nettoie le code avant validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => trim($this->input('code')),
            ]);
        }
    }
}