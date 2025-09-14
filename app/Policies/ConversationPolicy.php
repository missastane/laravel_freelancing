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
        \Log::info('in policy : '.$user->id);
        if (!$conversation->hasUser($user)) {
            return false;
        }
        return true;
    }
}
