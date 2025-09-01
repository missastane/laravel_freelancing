<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class AboutMeRequest extends FormRequest
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
            'gender' => 'required|in:1,2',
            'about_me' => 'required|string|min:10|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u'
        ];
    }
     public function attributes()
    {
        return [
            'gender' => 'جنسیت',
            'about_me' => 'درباره من',
        ];
    }
}
