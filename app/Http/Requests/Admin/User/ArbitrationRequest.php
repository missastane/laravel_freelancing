<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class ArbitrationRequest extends FormRequest
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
            'status' => 'integer|required|in:2,3,4,5',
            'freelancer_percent' => 'nullable|required_if:status,4|integer|in:1,100',
            'employer_percent' => 'nullable|required_if:status,4|integer|in:1,100',
            'description' => 'required|min:2|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u',
        ];
    }

    public function attributes()
    {
        return [
            'status' => 'وضعیت',
            'freelancer_percent' => 'سهم فریلنسر',
            'employer_percent' => 'سهم کارفرما',
            'description' => 'توضیحات ادمین'
        ];
    }
}
