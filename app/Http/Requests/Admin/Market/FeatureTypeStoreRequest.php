<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class FeatureTypeStoreRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:150|unique:feature_types,name|regex:/^[a-zA-Z0-9\- ]+$/u',
            'display_name' => 'required|min:2|string|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.\- ]+$/u',
            'description' => 'nullable|string|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
            'target_type' => 'required|in:project,proposal',
            'price' => 'required|numeric',
            'duration_days' => 'nullable|integer',
            'is_active' => 'required|in:1,2'
        ];
    }

     public function attributes()
    {
        return [
            'name' => 'نام ویژگی',
            'display_name' => 'نام نمایشی ویژگی',
            'description' => 'توضیح ویژگی',
            'target_type' => 'هدف ویژگی',
            'price' => 'قیمت',
            'duration_days' => 'تعداد روزهای فعال بودن',
            'is_active' => 'وضعیت فعال بودن'
        ];
    }
}
