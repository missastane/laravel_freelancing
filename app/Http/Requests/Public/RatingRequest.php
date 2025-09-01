<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
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
            'value' => 'required|integer|in:1,5',
        ];
    }

    public function attributes()
    {
        return [
            'value' => 'امتیاز',
            'order_id' => 'سفارش'
        ];
    }
}
