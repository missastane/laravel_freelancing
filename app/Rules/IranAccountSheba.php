<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranAccountSheba implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // example $value = ir12 1245 1258 6985 5789 2456 34
        $sheba = strtoupper($value);
        // exaple $sheba = IR12 1245 1258 6985 5789 2456 34
        if(!preg_match('/^IR\d{24}$/', $sheba)){
            $fail('فرمت شماره شبا صحیح نیست');
            return;
        }
        $reArranged = substr($sheba,4) . '1827' . substr($sheba,2,2);
        // example $reArranged = 1245 1258 6985 5789 2456 34 1827 12
        $numeric = '';
        foreach(str_split($reArranged) as $char){
            $numeric .= is_numeric($char) ? $char : ord($char) - 55;
        }
        if(bcmod($numeric, '97') != 1){
            $fail('شما شبای وارد شده معتبر نیست');
        }
    }
}
