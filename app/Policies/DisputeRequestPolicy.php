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

    public function withdrawn(User $user, DisputeRequest $disputeRequest)
    {
        return $disputeRequest->status == 1 && $user->id == $disputeRequest->raised_by;
    }

    public function delete(User $user, DisputeRequest $disputeRequest)
    {
        return $disputeRequest->status == 1 && $disputeRequest->raised_by == $user->id;
    }

    public function createTicket(User $user, DisputeRequest $disputeRequest)
    {
        return $disputeRequest->status == 1;
    }

    public function judge(User $user, DisputeRequest $disputeRequest)
    {
        return $disputeRequest->status == 1;
    }
}
