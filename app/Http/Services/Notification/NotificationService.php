<?php

namespace App\Http\Services\Notification;

use App\Models\User\Notification;
use App\Models\User\User;
use App\Repositories\Contracts\User\NotificationRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Throwable;

class NotificationService
{
    use ApiResponseTrait;
    protected User $user;
    public function __construct(protected NotificationRepositoryInterface $notificationRepository)
    {
        $this->user = auth()->user();
    }

    public function getUserNotifications(): Paginator
    {
        return $this->notificationRepository->getUserNotifications(auth()->id());
    }

    public function markNotificationAsRead(Notification $notification)
    {
        try {
            if (
                $notification->notifiable_type === User::class &&
                $notification->notifiable_id === auth()->id()
            ) {
                if ($notification->read_at === null) {
                    $this->notificationRepository->update($notification, ['read_at' => now()]);
                }
            }
        } catch (Throwable $throwable) {
            throw new Exception("خطا در علامت زدن اعلان به عنوان خوانده شده", 500);
        }
    }
    public function showNotification(Notification $notification)
    {
        $this->markNotificationAsRead($notification);
        return $this->notificationRepository->showWithRelations($notification);
    }

    public function deleteNotification(Notification $notification)
    {
        return $this->notificationRepository->delete($notification);
    }
}