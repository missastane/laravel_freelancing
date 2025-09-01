<?php

namespace App\Policies;

use App\Models\User\Notification;
use App\Models\User\User;

class NotificationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, Notification $notification)
    {
        return $notification->notifiable_type === User::class &&
                $notification->notifiable_id === $user->id;
    }
}
