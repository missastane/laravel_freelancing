<?php

namespace App\Policies;

use App\Models\Ticket\TicketMessage;
use App\Models\User\User;

class TicketMessagePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user,TicketMessage $ticketMessage)
    {
        return $ticketMessage->author_type == 3 || $ticketMessage->author_id == $user->id;
    }
}
