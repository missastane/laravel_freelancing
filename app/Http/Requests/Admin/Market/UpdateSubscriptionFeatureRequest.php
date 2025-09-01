<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionFeatureRequest extends FormRequest
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
            'feature_key' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'feature_value' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'feature_value_type' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'is_limited' => 'required|integer|in:1,2',
        ];
    }

    public function attributes()
    {
        return [
            'feature_key' => 'ویژگی',
            'feature_value' => 'مقدار',
            'feature_value_type' => 'نوع ویژگی',
            'is_limited' => 'آیا ویژگی محدودکننده است'
        ];
    }
}
