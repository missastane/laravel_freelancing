<?php

namespace App\Policies;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;

class ProposalPolicy
{
    public function store(Project $project)
    {
        return $project->status == 1;
    }

    public function update(Proposal $proposal)
    {
        return $proposal->status == 1;
    }
    public function withdraw(Proposal $proposal)
    {
        return $proposal->status == 1;
    }

    public function approve(Proposal $proposal)
    {
        return $proposal->status == 1;
    }
}
