<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Repositories\Contracts\Market\ProposalMilestoneRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;

class ProposalMilestoneRepository extends BaseRepository implements ProposalMilestoneRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    public function __construct(ProposalMilestone $model)
    {
        parent::__construct($model);
    }

    public function deleteProposlMilestones(Proposal $proposal)
    {
        return $proposal->milestones()->delete();
    }


}