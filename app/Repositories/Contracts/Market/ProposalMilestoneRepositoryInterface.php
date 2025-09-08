<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Proposal;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface ProposalMilestoneRepositoryInterface extends ShowableRepositoryInterface,CreatableRepositoryInterface
{
    public function deleteProposlMilestones(Proposal $proposal);
}