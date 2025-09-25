<?php

namespace App\Policies;

use App\Models\Market\OrderItem;
use App\Models\User\User;

class OrderItemPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function storeDisputeRequest(User $user, OrderItem $orderItem)
    {
        if (!in_array($orderItem->status,[2,3])) {
            return false; // item must be in progress or completed(delivered)
        }
        $order = $orderItem->order;
        $isValidRole = match ($user->active_role) {
            'freelancer' => $user->id === $order->freelancer_id,
            'employer' => $user->id === $order->employer_id,
            default => false,
        };

        if (!$isValidRole) {
            return false;
        }
        return true;
    }
}
