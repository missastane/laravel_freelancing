<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PlainTextRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_string($value) ||
            !preg_match(RegexPatterns::PLAIN_TEXT, $value) ||
            $value !== strip_tags($value)
        ) {
            $fail(__('validation.custom.plain_text_only', [
                'attribute' => $attribute,
            ]));
        }
    }
}