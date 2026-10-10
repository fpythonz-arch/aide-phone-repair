<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/** Adresse e-mail non utilisée, sans tenir compte de la casse. */
class UniqueEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower((string) $value)])->exists()) {
            $fail('Cette adresse e-mail est déjà utilisée.');
        }
    }
}
