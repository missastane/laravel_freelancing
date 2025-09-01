<?php

namespace App\Http\Requests\Admin\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class DisputeTicketRequest extends FormRequest
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
            'priority_id' => 'required|exists:ticket_priorities,id',
            'department_id' => 'required|exists:ticket_departments,id',
        ];
    }

     public function attributes()
    {
        return [
            'priority_id' => 'اولویت تیکت',
            'department_id' => 'دسته تیکت',
        ];
    }
}
