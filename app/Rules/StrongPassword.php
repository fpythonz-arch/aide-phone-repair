<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Mot de passe : 10 caractères minimum, au moins une lettre et un chiffre. */
class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (mb_strlen($value) < 10) {
            $fail('Le mot de passe doit contenir au moins 10 caractères.');
        } elseif (! preg_match('/\p{L}/u', $value) || ! preg_match('/\d/', $value)) {
            $fail('Le mot de passe doit contenir au moins une lettre et un chiffre.');
        }
    }
}
