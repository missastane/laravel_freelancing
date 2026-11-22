<?php

namespace App\Http\Requests\Admin\Market;

use App\Rules\SafeTitleRule;
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
                new SafeTitleRule(),
                Rule::unique('subscriptions', 'name')
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
