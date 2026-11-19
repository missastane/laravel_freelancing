<?php

namespace App\Http\Requests\Admin\Locale;

use App\Rules\NameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
                new NameRule(),
                Rule::unique('provinces', 'name')
                    ->ignore($this->route('province')),
            ],
        ];
    }
}