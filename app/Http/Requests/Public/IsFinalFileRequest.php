<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class IsFinalFileRequest extends FormRequest
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
            'is_final_file' => 'required|in:1,2',
            'order_item_id' => 'required|exists:order_items,id'
        ];
    }
}
