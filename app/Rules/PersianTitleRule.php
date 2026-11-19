<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PersianTitleRule implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (!preg_match(RegexPatterns::PERSIAN_TITLE, $value)) {
            $fail(__('validation.custom.persian_title'));
        }
    }
}