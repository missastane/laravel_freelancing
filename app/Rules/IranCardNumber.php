<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranCardNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // card number must be included 16 digits
        if(!preg_match('/^\d{16}$/',$value)){
            $fail('شماره کارت باید 16 رقمی باشد');
            return;
        }
        // Luhn algorithm

        $sum = 0;
        for($i=0;$i < 16; $i++){
            $digit = (int) $value[$i];
            // each digit with an even index is multiplied by 2:
            $digit *= ($i % 2 === 0) ? 2 : 1;
            // if the result of multiplication is greater than 9, we subtract 9 units from it:
            if($digit > 9) $digit -= 9;
            $sum += $digit;
        }
        // 
        if($sum % 10 !== 0){
            $fail('شماره کارت وارد شده معتبر نیست');
        }
    }
}
