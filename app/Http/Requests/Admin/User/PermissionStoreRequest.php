<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class PermissionStoreRequest extends FormRequest
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
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|distinct|exists:permissions,id',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }

    public function attributes()
    {
        return ['permission' => 'سطح دسترسی'];
    }
}
