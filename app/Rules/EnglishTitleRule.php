<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnglishTitleRule implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (!preg_match(RegexPatterns::ENGLISH_TITLE, $value)) {
            $fail(__('validation.custom.english_title'));
        }
    }
}