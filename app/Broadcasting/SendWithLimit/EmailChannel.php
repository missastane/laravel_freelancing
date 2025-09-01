<?php

namespace App\Broadcasting\SendWithLimit;

use App\Http\Services\Notification\SubscriptionUsageManagerService;
use App\Models\User\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChannel
{
    public function __construct(protected SubscriptionUsageManagerService $service)
    {
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$this->service->canUse('email')) {
            return; // کاربر اجازه نداره
        }
        if (!method_exists($notification, 'toMail')) {
            return;
        }
        $mailMessage = $notification->toMail($notifiable);

        if (!$mailMessage) {
            return; // نوتی چیزی برای ارسال ایمیل نداده
        }

        try {
            // ارسال واقعی ایمیل
            Mail::to($notifiable->email)->send($mailMessage);

            // اگر خطایی نداد، مصرف ایمیل رو زیاد می‌کنیم
            $this->service->increamentUsage('email');
        } catch (\Throwable $e) {
            Log::error('EmailChannel error: '.$e->getMessage());
        }
    }
}
