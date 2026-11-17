<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SystemNameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_string($value) ||
            !preg_match(RegexPatterns::SYSTEM_NAME, $value)
        ) {
            $fail(__('validation.custom.system_name', [
                'attribute' => $attribute,
            ]));
        }
    }
}