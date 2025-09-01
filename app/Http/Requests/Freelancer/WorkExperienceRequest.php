<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class WorkExperienceRequest extends FormRequest
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
            'province_id' => 'required|exists:provinces,id',
            'company_name' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
            'position' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
            'start_year' => 'required|numeric',
            'end_year' => 'required|numeric',
        ];
    }

    public function attributes()
    {
        return [
            'province_id' => 'استان',
            'company_name' => 'نام شرکت/سازمان',
            'position' => 'سمت',
            'start_year' => 'سال شروع',
            'end_year' => 'سال پایان',
        ];
    }
}
