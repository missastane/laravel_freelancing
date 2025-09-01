<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;

class RevisionFileItemRequest extends FormRequest
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
            'revision_note' => 'required|max:300|min:5|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u'
        ];
    }

    public function attributes()
    {
        return ['revision_note' => 'توضیحات'];
    }
}
