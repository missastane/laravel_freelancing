<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
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
            'name' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ ]+$/u',
            'amount' => 'required|numeric',
            'duration_days' => 'required|integer',
            'commission_rate' => 'required|integer|in:0,100',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'نام',
            'amount' => 'قیمت',
            'duration_days' => 'تعداد روزهای اشتراک',
            'commission_rate' => 'درصد کارمزد سایت',
        ];
    }
}
