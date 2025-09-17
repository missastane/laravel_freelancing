<?php

namespace App\Policies;

use App\Models\Market\Order;
use App\Models\User\User;

class OrderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function addRate(User $user, Order $order): bool
    {
        if ($user->active_role === 'admin') {
            return false;
        }

        return match ($user->active_role) {
            'freelancer' => $user->id === $order->freelancer_id,
            'employer' => $user->id === $order->employer_id,
            default => false,
        };
    }

}
