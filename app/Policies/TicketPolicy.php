<?php

namespace App\Policies;

use App\Models\Ticket\Ticket;
use App\Models\User\User;

class TicketPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }
}
