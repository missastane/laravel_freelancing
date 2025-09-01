<?php

namespace App\Http\Requests\Admin\Locale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'name' => [
                    'required',
                    'regex:/^[ا-یء-ي ]+$/u',
                    Rule::unique('cities')
                        ->where(fn($query) => $query->where('province_id', $this->route('province')->id))
                ],
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        }
        return [
            'name' => [
                'required',
                'regex:/^[ا-یء-ي ]+$/u',
                Rule::unique('cities')
                    ->where(fn($query) => $query->where('province_id', $this->province_id))->ignore($this->route('city'))
            ],
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }

}
