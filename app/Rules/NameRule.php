<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_string($value) ||
            !preg_match(RegexPatterns::NAME, $value)
        ) {
            $fail(__('validation.custom.name_only', [
                'attribute' => $attribute,
            ]));
        }
    }
}