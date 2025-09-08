<?php

namespace App\Policies;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;

class ProposalPolicy
{

    public function show(User $user, Proposal $proposal)
    {
        return $proposal->freelancer_id == $user->id && $user->active_role === 'freelancer' || $proposal->project->user_id == $user->id  && $user->active_role === 'employer' || $user->active_role === 'admin';
    }
    public function update(User $user, Proposal $proposal)
    {
        return $proposal->status == 1 && $proposal->freelancer_id == $user->id;
    }
    public function withdraw(User $user, Proposal $proposal)
    {
        return $proposal->status == 1 && $proposal->freelancer_id == $user->id;
    }

    public function approve(User $user, Proposal $proposal)
    {
        return $proposal->status == 1;
    }
}
