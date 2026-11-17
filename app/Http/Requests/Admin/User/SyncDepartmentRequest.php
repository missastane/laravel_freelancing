<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncDepartmentRequest extends FormRequest
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
        $admin = $this->route('admin');

        return [
            'departments' => 'present|array',
            'departments.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('ticket_departments', 'id')
                    ->where(function ($query) use ($admin) {
                        $query->where('status', 1)
                            ->orWhereIn(
                                'id',
                                $admin->departments()->pluck('ticket_departments.id')
                            );
                    }),
            ],
        ];
    }

    public function attributes()
    {
        return ['departments' => 'دپارتمان'];
    }
}
