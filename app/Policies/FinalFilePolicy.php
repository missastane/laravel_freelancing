<?php

namespace App\Policies;

use App\Models\Market\FinalFile;
use App\Models\User\User;

class FinalFilePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function approve(User $user, FinalFile $finalFile)
    {
        $orderItem = $finalFile->orderItem;
        $order = $orderItem->order;
         return $order->employer_id == $user->id
        && in_array($order->status, [1, 2]) // order: pending or in progress
        && $orderItem->status == 3;
    }
}
