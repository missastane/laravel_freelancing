<?php

namespace App\Http\Requests\Admin\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketDepartmentRequest extends FormRequest
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
            'name' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Zء-ي ]+$/u'
        ];
    }
}
