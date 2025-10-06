<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddDisputeRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $freelancer, $employer, $disputer;
    public int $orderItemId;
    /**
     * Create a new event instance.
     */
    public function __construct($freelancer, $employer, $disputer, int $orderItemId)
    {
        $this->freelancer = $freelancer;
        $this->employer = $employer;
        $this->disputer = $disputer;
        $this->orderItemId = $orderItemId;
    }

}
