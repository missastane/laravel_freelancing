<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'message' => 'nullable|required_without:files|string|max:1000',
            'files' => 'nullable|required_without:message',
            'files.*' => 'nullable|file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:2048'
        ];
    }

     public function attributes()
    {
        return [
            'message' => 'پیام',
            'files.*' => 'فایل',
            'files' => 'فایل ها',
        ];
    }
}
