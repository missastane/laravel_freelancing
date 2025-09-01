<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class ProposalRepository extends BaseRepository implements ProposalRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(Proposal $proposal)
    {
        parent::__construct($proposal);
    }

    public function showProposal(Proposal $proposal): Proposal
    {
        return $this->showWithRelations($proposal, ['project']);
    }

    public function getProposals(Project $project = null, User $user = null, array $data): Paginator|Project
    {
        $user = $user ?? auth()->user();
        $freelancerProposals = $this->model->where('freelancer_id', $user->id)->filterByStatus($data)->with('project')
            ->orderBy('created_at', 'desc')->simplePaginate(20);
        $employerProposal = $project->load('proposals');
        $proposals = $user->active_role === 'freelancer' ? $freelancerProposals : $employerProposal;
        return $proposals;
    }

    public function updateWhere(array $conditions, array $data): int
    {
        return $this->model->where($conditions)->update($data);
    }

    public function getProjectProposalsStats(Project $project): array
    {
        $query = DB::table('proposals')->where('project_id', $project->id);

        return [
            'min_days' => $query->min('total_duration_time'),
            'max_days' => $query->max('total_duration_time'),
            'min_price' => $query->min('total_amount'),
            'max_price' => $query->max('total_amount')
        ];
    }


}