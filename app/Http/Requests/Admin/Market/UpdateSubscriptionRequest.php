<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
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
        $subscription = $this->route('subscription');
        return [
            'name' => [
                'required',
                'min:2',
                'max:255',
                'regex:/^[ا-یa-zA-Z0-9\-۰-۹ ]+$/u',
                Rule::unique('subscriptions', 'name')
                    ->where(function ($query) use ($subscription) {
                        return $query->where('target_type', $subscription->target_type);
                    })
                    ->ignore($subscription->id), 
            ],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'نام',
        ];
    }
}
