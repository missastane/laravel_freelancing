<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;

class UserEducationRequest extends FormRequest
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
            'university_name' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
            'field_of_study' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
            'start_year' => 'required|numeric',
            'end_year' => 'required|numeric',
        ];
    }

     public function attributes()
    {
        return [
            'province_id' => 'استان',
            'university_name' => 'نام دانشگاه',
            'field_of_study' => 'رشته تحصیلی',
            'start_year' => 'سال شروع',
            'end_year' => 'سال پایان',
        ];
    }
}
