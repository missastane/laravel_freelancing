<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckWithdrawalAmount implements ValidationRule
{
   
    public function __construct(protected int|float|null $walletBalance = null)
    {
        $this->walletBalance = auth()->user()->wallet->balance;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minRemain = 2000; //tooman
        $maxWithdrawable = $this->walletBalance - $minRemain;
        if($value > $maxWithdrawable){
            $fail("مبلغ وارد شده بیشتر از موجودی کیف پول شماست. حداکثر مبلغ مجاز : {$maxWithdrawable} تومان");
        }
    }
}
