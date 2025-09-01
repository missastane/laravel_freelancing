<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'amount' => 'required|integer|min:20000',
            'description' => 'nullable|string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u'
        ];
    }

    public function attributes()
    {
        return[
            'amount' => 'مبلغ درخواستی',
            'description' => 'توضیحات'
        ];
    }
}
