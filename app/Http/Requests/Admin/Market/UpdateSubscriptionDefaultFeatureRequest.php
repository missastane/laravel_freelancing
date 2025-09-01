<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionDefaultFeatureRequest extends FormRequest
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
            'max_target_per_month' => 'required|integer',
            'max_notification_per_month' => 'required|integer',
            'max_email_per_month' => 'required|integer',
            'max_sms_per_month' => 'required|integer',
            'max_view_deatils_per_month' => 'required|integer',
        ];
    }

     public function attributes()
    {
        return [
            'max_target_per_month' => 'حداکثر تعداد پیشنهاد یا پروژه در ماه',
            'max_notification_per_month' => 'حداکثر ارسال اعلان در ماه',
            'max_email_per_month' => 'حداکثر ارسال ایمیل در ماه',
            'max_sms_per_month' => 'حداکثر ارسال پیامک در ماه',
            'max_view_deatils_per_month' => 'حداکثر تعداد دفعات مشاهده جزئیات پیشنهاد یا پروژه در ماه'
        ];
    }
}
