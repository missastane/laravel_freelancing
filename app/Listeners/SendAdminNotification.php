<?php

namespace App\Listeners;

use App\Events\AddWithDrawalRequest;
use App\Models\User\User;
use App\Notifications\NewWithDrawalRequestNot;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendAdminNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AddWithDrawalRequest $event): void
    {
        $user = $event->user;
        $admins = $this->userRepository->getAdmins();
        Notification::send($admins,new NewWithDrawalRequestNot($user));
    }
}
