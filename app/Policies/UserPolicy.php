<?php

namespace App\Policies;

use App\Models\User\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function assignRole(User $authUser, User $targetUser): bool
    {
        return $targetUser->user_type === 2;
    }

}
