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
        if ($this->isMethod('post')) {
            return [
                'description' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milestones' => ['required', 'array', 'min:1'],
                'milestones.*.title' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u'],
                'milestones.*.description' => ['required', 'string', 'min:5', 'max:600', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milestones.*.amount' => ['required', 'numeric'],
                'milestones.*.duration_time' => ['required', 'integer'],
            ];
        } else {
            return [
                'description' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milestones' => ['nullable', 'array'],
                'milestones.*.title' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u'],
                'milestones.*.description' => ['required', 'string', 'min:5', 'max:600', 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u'],
                'milestones.*.amount' => ['required', 'numeric'],
                'milestones.*.duration_time' => ['required', 'integer'],
            ];
        }

    }

    public function attributes()
    {
        return [
            'description' => 'توضیحات',
            'milestones' => 'مراحل پشنهاد',
            'milestones.*.title' => 'عنوان مرحله',
            'milestones.*.description' => 'توضیح مرحله',
            'milestones.*.amount' => 'مبلغ',
            'milestones.*.duration_time' => 'زمان مرحله',
        ];
    }
}
