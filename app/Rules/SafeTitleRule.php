<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeTitleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_string($value) ||
            !preg_match(RegexPatterns::SAFE_TITLE, $value)
        ) {
            $fail(__('validation.custom.safe_title', [
                'attribute' => $attribute,
            ]));
        }
    }
}