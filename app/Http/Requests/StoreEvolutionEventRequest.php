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
            'symptom_id' => 'required|integer|exists:symptoms,id',
            'component_id' => 'nullable|integer|exists:components,id',
            'event_type' => 'required|string|in:symptom_worsening,symptom_improvement,new_symptom_appeared,repair_attempt,component_failure,temporary_fix,recurring_issue',
            'description' => 'required|string|min:10|max:2000',
            'severity_before' => 'required|integer|between:1,5',
            'severity_after' => 'required|integer|between:1,5',
            'device_model' => 'required|string|max:100',
            'device_brand' => 'required|string|max:50',
            'repair_attempted' => 'required|boolean',
            'repair_successful' => 'nullable|boolean|required_if:repair_attempted,true',
            'time_elapsed_days' => 'nullable|integer|min:1|max:365',
            'environmental_factors' => 'nullable|array',
            'environmental_factors.*' => 'string|max:50',
            'user_notes' => 'nullable|string|max:1000',
            'logged_by' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'symptom_id.required' => 'Le symptôme associé est obligatoire.',
            'symptom_id.exists' => 'Le symptôme sélectionné n\'existe pas.',
            'event_type.required' => 'Le type d\'événement est obligatoire.',
            'event_type.in' => 'Le type d\'événement doit être l\'un des suivants : aggravation, amélioration, nouveau symptôme, tentative de réparation, panne composant, réparation temporaire, problème récurrent.',
            'severity_before.required' => 'La sévérité avant l\'événement est obligatoire.',
            'severity_after.required' => 'La sévérité après l\'événement est obligatoire.',
            'repair_successful.required_if' => 'Veuillez indiquer si la réparation a réussi.',
            'description.min' => 'La description doit contenir au moins 10 caractères.',
        ];
    }

    public function attributes(): array
    {
        return [
            'symptom_id' => 'symptôme',
            'component_id' => 'composant',
            'event_type' => 'type d\'événement',
            'severity_before' => 'sévérité initiale',
            'severity_after' => 'sévérité finale',
            'device_model' => 'modèle d\'appareil',
            'device_brand' => 'marque',
            'repair_attempted' => 'tentative de réparation',
            'repair_successful' => 'réparation réussie',
            'time_elapsed_days' => 'temps écoulé',
        ];
    }

    /**
     * Prépare les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('repair_attempted') && !$this->boolean('repair_attempted')) {
            $this->merge([
                'repair_successful' => null,
            ]);
        }
    }
}