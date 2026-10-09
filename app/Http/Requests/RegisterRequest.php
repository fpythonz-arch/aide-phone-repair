<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'workshop_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower((string) $value)])->exists()) {
                        $fail('Cette adresse e-mail est déjà utilisée.');
                    }
                },
            ],
            'password' => [
                'required', 'string', 'max:200', 'confirmed',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $value = (string) $value;

                    if (mb_strlen($value) < 10) {
                        $fail('Le mot de passe doit contenir au moins 10 caractères.');
                    } elseif (! preg_match('/\p{L}/u', $value) || ! preg_match('/\d/', $value)) {
                        $fail('Le mot de passe doit contenir au moins une lettre et un chiffre.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Votre nom est obligatoire.',
            'workshop_name.required' => 'Le nom de l\'atelier est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
