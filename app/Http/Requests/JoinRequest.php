<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;
use App\Rules\UniqueEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/** Acceptation d'une invitation : seuls nom, e-mail et mot de passe viennent du client. */
class JoinRequest extends FormRequest
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
            'email' => ['required', 'email:rfc', 'max:255', new UniqueEmail()],
            'password' => ['required', 'string', 'max:200', 'confirmed', new StrongPassword()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Votre nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
