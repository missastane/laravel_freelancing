<?php

namespace App\Http\Requests\Admin\Market;

use App\Rules\SafeTitleRule;
use App\Rules\SystemNameRule;
use App\Rules\PersianTitleRule;
use App\Rules\PlainTextRule;
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
            'name' => ['required', 'min:2', 'max:255', new SafeTitleRule()],
            'amount' => 'required|numeric',
            'duration_days' => 'required|integer',
            'commission_rate' => 'required|integer|min:0|max:99',
            'max_target_per_month' => 'required|integer',
            'max_notification_per_month' => 'required|integer',
            'max_email_per_month' => 'required|integer',
            'max_sms_per_month' => 'required|integer',
            'max_view_deatils_per_month' => 'required|integer',

            'features' => ['nullable', 'array'],
            'features.*' => ['required', 'array'],

            'features.*.feature_key' => ['required', 'string', new SystemNameRule()],
            'features.*.feature_persian_key' => ['required', 'string', new PersianTitleRule()],
            'features.*.feature_value' => ['required', 'string', new PlainTextRule()],
            'features.*.feature_value_type' => ['required', 'string', new SystemNameRule()],
            'features.*.is_limited' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'amount' => 'قیمت',
            'duration_days' => 'تعداد روزهای اشتراک',
            'commission_rate' => 'درصد کارمزد سایت',
            'max_target_per_month' => 'حداکثر تعداد پیشنهاد یا پروژه در ماه',
            'max_notification_per_month' => 'حداکثر ارسال اعلان در ماه',
            'max_email_per_month' => 'حداکثر ارسال ایمیل در ماه',
            'max_sms_per_month' => 'حداکثر ارسال پیامک در ماه',
            'max_view_deatils_per_month' => 'حداکثر تعداد دفعات مشاهده جزئیات پیشنهاد یا پروژه در ماه',
            'features' => 'ویژگی‌ها',
            'features.*.feature_key' => 'شناسه ویژگی',
            'features.*.feature_persian_key' => 'عنوان فارسی ویژگی',
            'features.*.feature_value' => 'مقدار ویژگی',
            'features.*.feature_value_type' => 'نوع ویژگی',
            'features.*.is_limited' => 'آیا ویژگی محدودکننده است',
        ];
    }
}
