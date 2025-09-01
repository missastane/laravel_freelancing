<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Proposal;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface ProposalMilstoneRepositoryInterface extends ShowableRepositoryInterface
{
    public function create(Proposal $proposal, array $data);
    public function deleteProposlMilestones(Proposal $proposal);
}