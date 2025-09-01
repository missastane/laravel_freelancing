<?php

namespace App\Listeners;

use App\Events\AddWithDrawalRequest;
use App\Models\User\User;
use App\Notifications\NewWithDrawalRequestNot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendAdminNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AddWithDrawalRequest $event): void
    {
        $user = $event->user;
        $admins = User::whereHas('roles',function($query){
            $query->where('name','admin');
        })->get();
        Notification::send($admins,new NewWithDrawalRequestNot($user));
    }
}
