<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|min:8',
            'new_password' => ['required', 'unique:users,password', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ];
    }
    public function attributes()
    {
        return [
            'current_password' => 'کلمه عبور فعلی',
            'new_password' => 'کلمه عبور جدید',
        ];
    }
}
