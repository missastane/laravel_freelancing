<?php

namespace App\Http\Requests\Admin\User;

use App\Rules\SystemNameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
        $route = Route::currentRouteName();

        if ($route === 'admin.permission.store') {
            return [
                'name' => [
                    'required',
                    'string',
                    new SystemNameRule(),
                    'unique:permissions,name',
                ],
            ];
        } elseif ($route === 'admin.permission.sync-roles') {
            return [
                'roles' => 'required|array',
                'roles.*' => 'required|exists:roles,id',
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                new SystemNameRule(),
                Rule::unique('permissions', 'name')->ignore($this->route('permission')),
            ],
        ];
    }
    public function attributes()
    {
        return [
            'roles' => 'نقش ها',
            'roles.*' => 'نقش',
        ];
    }
}
