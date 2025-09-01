<?php

namespace App\Http\Requests\Public;

use App\Rules\CheckWithdrawalAmount;
use App\Rules\IranAccountSheba;
use App\Rules\IranCardNumber;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_number_sheba' => ['required', new IranAccountSheba()],
            'card_number' => ['required', new IranCardNumber()],
            'bank_name' => 'required|string|regex:/^[ا-یء-ي ]+$/u',
            'amount' => ['required','integer', new CheckWithdrawalAmount()]
        ];
    }

    public function attributes()
    {
        return [
            'account_number_sheba' => 'شماره شبای بانکی',
            'card_number' => 'شماره کارت',
            'bank_name' => 'نام بانک',
            'amount' => 'مبلغ'
        ];
    }
}
