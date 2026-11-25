<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_token' => [
                'required',
                'string',
                'size:64',
                'regex:/^[a-f0-9]{64}$/i',
            ],
            'code' => [
                'required',
                'string',
                'regex:/^(?:\d{6}|[A-Fa-f0-9]{10})$/',
            ],
        ];
    }
}
