<?php

namespace App\Http\Requests\Profile;

use App\Rules\NationalCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'last_name' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'national_code' => ['nullable', new NationalCode(), Rule::unique('users')->ignore($this->user()->national_code, 'national_code')],
            'profile_photo_path' => 'nullable|image|mimes:png,jpg,jpeg,gif',
            'birth_date' => 'required|numeric',
        ];
    }

     public function attributes()
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'national_code' => 'کد ملی',
            'profile_photo_path' => 'تصویر آواتار',
            'birth_date' => 'تاریخ تولد',
        ];
    }
}
