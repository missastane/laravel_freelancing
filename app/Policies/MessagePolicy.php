<?php

namespace App\Policies;

use App\Models\Market\Message;
use App\Models\User\User;

class MessagePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user,Message $message)
    {
        return $message->sender_id == $user->id;
    }
}
