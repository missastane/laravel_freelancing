<?php

namespace App\Policies;

use App\Models\User\DisputeRequest;
use App\Models\User\User;

class DisputeRequestPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(DisputeRequest $disputeRequest, User $user)
    {
        return $disputeRequest->status == 1 && $disputeRequest->raised_by == $user->id;
    }
}
