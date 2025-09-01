<?php

namespace App\Broadcasting\SendWithLimit;

use App\Http\Services\Notification\SubscriptionUsageManagerService;
use App\Models\User\User;
use App\Repositories\Contracts\User\NotificationRepositoryInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NotificationChannel
{

    public function __construct(
        protected SubscriptionUsageManagerService $service,
        protected NotificationRepositoryInterface $notificationRepository
        )
    {
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$this->service->canUse('notification')) {
            return; // محدودیت مصرف اجازه نمیده
        }

        if (!method_exists($notification, 'toArray')) {
            return;
        }
        $data = $notification->toArray($notifiable);

        if (!$data) {
            return; // چیزی برای ذخیره در دیتابیس نیست
        }

        try {
            // ثبت در دیتابیس (با استفاده از built-in database channel)
            $this->notificationRepository->create([
                'type' => get_class($notification),
                'data' => $data,
                'read_at' => null,
            ]);

            // اگه خطایی نداد، مصرف رو زیاد کن
            $this->service->increamentUsage('notification');
        } catch (\Throwable $e) {
            Log::error('NotificationChannel error: ' . $e->getMessage());
        }
    }
}
