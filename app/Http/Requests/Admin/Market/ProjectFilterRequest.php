<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class ProjectFilterRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'regex:/^(pending|processing|completed|canceled)(, *(pending|processing|completed|canceled))*$/'],
            'category_id' => 'nullable|exists:project_categories,id'
        ];
    }
    public function attributes()
    {
        return [
            'status' => 'وضعیت',
            'category_id' => 'دسته بندی'
        ];
    }
}
