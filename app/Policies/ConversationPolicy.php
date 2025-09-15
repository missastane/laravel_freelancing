<?php

namespace App\Policies;

use App\Models\Market\Conversation;
use App\Models\User\User;

class ConversationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function checkMembership(User $user, Conversation $conversation)
    {
        if (!$conversation->hasUser($user)) {
            return false;
        }
        return true;
    }

    public function checkSendable(User $user, Conversation $conversation)
    {
        if (!$conversation->hasUser($user) || $conversation->status == 2) {
            return false;
        }
        return true;
    }
}
