<?php

namespace App\Http\Requests\Admin\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'ticket_type' => 'required|in:1,3',
            'subject' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
            'message' => 'required|min:2|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u',
            'files' => 'nullable|required_without:message',
            'files.*' => 'nullable|file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:2048'
        ];
    }

    public function attributes()
    {
        return [
            'priority_id' => 'اولویت تیکت',
            'department_id' => 'دسته تیکت',
            'order_id' => 'سفارش',
            'ticket_type' => 'نوع تیکت',
            'subject' => 'موضوع',
            'message' => 'پیغام تیکت',
            'files.*' => 'فایل',
            'files' => 'فایل ها',
        ];
    }
}
