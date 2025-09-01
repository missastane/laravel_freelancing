<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ProposalRequest extends FormRequest
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
        $route = Route::currentRouteName();
        if ($route === 'proposal.store') {
            return [
                'description' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milstones' => ['required', 'array', 'min:1'],
                'milstones.*.title' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u'],
                'milstones.*.description' => ['required', 'string', 'min:5', 'max:600', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milstones.*.amount' => ['required', 'numeric'],
                'milstones.*.duration_time' => ['required', 'integer'],
            ];
        } else {
            return [
                'description' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milstones' => ['nullable', 'array'],
                'milstones.*.title' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u'],
                'milstones.*.description' => ['required', 'string', 'min:5', 'max:600', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milstones.*.amount' => ['required', 'numeric'],
                'milstones.*.duration_time' => ['required', 'integer'],
            ];
        }

    }

    public function attributes()
    {
        return [
            'description' => 'توضیحات',
            'milstones' => 'مراحل پشنهاد',
            'milstones.*.title' => 'عنوان مرحله',
            'milstones.*.description' => 'توضیح مرحله',
            'milstones.*.amount' => 'مبلغ',
            'milstones.*.duration_time' => 'زمان مرحله',
        ];
    }
}
