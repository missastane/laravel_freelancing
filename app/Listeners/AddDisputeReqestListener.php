<?php

namespace App\Listeners;

use App\Events\AddDisputeRequestEvent;
use App\Notifications\AddDisputeRequestNotif;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class AddDisputeReqestListener
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
    public function handle(AddDisputeRequestEvent $event): void
    {
        $freelancer = $event->freelancer;
        $employer = $event->employer;
        Notification::send([$freelancer,$employer],new AddDisputeRequestNotif($event->disputer));
    }
}
