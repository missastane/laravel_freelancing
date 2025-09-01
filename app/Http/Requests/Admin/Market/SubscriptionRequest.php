<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
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
            'name' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ ]+$/u',
            'amount' => 'required|numeric',
            'duration_days' => 'required|integer',
            'commission_rate' => 'required|integer|in:0,100',
            'target_type' => 'required|in:1,2', // 1 => project,  2 => proposal
            'max_target_per_month' => 'required|integer',
            'max_notification_per_month' => 'required|integer',
            'max_email_per_month' => 'required|integer',
            'max_sms_per_month' => 'required|integer',
            'max_view_deatils_per_month' => 'required|integer',
            'features' => ['nullable', 'array'],
            'features.*.feature_key' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'features.*.feature_value' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'features.*.feature_value_type' => 'required|string|regex:/^[ا-یء-ي۰-۹ ]+$/u',
            'features.*.is_limited' => 'required|integer|in:1,2',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'نام',
            'amount' => 'قیمت',
            'duration_days' => 'تعداد روزهای اشتراک',
            'commission_rate' => 'درصد کارمزد سایت',
            'target_type' => 'طرح متعلق به کارفرماست یا فریلنسر',
            'max_target_per_month' => 'حداکثر تعداد پیشنهاد یا پروژه در ماه',
            'max_notification_per_month' => 'حداکثر ارسال اعلان در ماه',
            'max_email_per_month' => 'حداکثر ارسال ایمیل در ماه',
            'max_sms_per_month' => 'حداکثر ارسال پیامک در ماه',
            'max_view_deatils_per_month' => 'حداکثر تعداد دفعات مشاهده جزئیات پیشنهاد یا پروژه در ماه',
            'features' => 'ویژگی',
            'features.*.feature_key' => 'ویژگی',
            'features.*.features.*.feature_value' => 'مقدار',
            'features.*.feature_value_type' => 'نوع ویژگی',
            'features.*.is_limited' => 'آیا ویژگی محدودکننده است'

        ];
    }
}
