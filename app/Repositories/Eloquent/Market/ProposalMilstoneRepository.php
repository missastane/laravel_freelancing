<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilstone;
use App\Repositories\Contracts\Market\ProposalMilstoneRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;

class ProposalMilstoneRepository extends BaseRepository implements ProposalMilstoneRepositoryInterface
{
    use HasShowTrait;
    public function __construct(ProposalMilstone $model)
    {
        parent::__construct($model);
    }

    public function create(Proposal $proposal,array $data)
    {
        $data['proposal_id'] = $proposal->id;
        return $this->model->create($data);
    }

    public function deleteProposlMilestones(Proposal $proposal)
    {
        return $proposal->milstones()->delete();
    }


}