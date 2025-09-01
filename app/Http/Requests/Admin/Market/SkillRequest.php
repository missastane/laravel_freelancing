<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'persian_title' => 'required|unique:skills,persian_title|regex:/^[ا-یء-ي۰-۹ ]+$/u',
                'original_title' => 'required|unique:skills,original_title|regex:/^[a-zA-Z0-9 ]+$/u',
            ];
        }
         return [
            'persian_title' => ['required','regex:/^[ا-یء-ي۰-۹ ]+$/u',Rule::unique('skills','persian_title')->ignore($this->route('skill'))],
            'original_title' => ['required','regex:/^[a-zA-Z0-9 ]+$/u',Rule::unique('skills','original_title')->ignore($this->route('skill'))],
        ];

    }

    public function attributes()
    {
        return [
            'persian_title' => 'عنوان فارسی',
            'original_title' => 'عنوان انگلیسی',
        ];
    }
}
