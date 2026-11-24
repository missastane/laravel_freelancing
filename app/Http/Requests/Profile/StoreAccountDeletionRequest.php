<?php

namespace App\Http\Requests\Profile;

use App\Rules\PlainTextRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000', new PlainTextRule()],
        ];
    }
}