<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\EmployerProposalsResource;
use App\Http\Resources\Market\FreelancerProposalResource;
use App\Http\Resources\Market\ProposalResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
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

    public function existsForProjectAndFreelancer($projectId, $freelancerId): bool
    {
        return $this->model
            ->where('project_id', $projectId)
            ->where('freelancer_id', $freelancerId)
            ->exists();
    }
    public function showProposal(Proposal $proposal): Proposal
    {
        return $this->showWithRelations($proposal, ['project','milestones']);
    }

    public function getProposals(string $status)
    {
        $user = $user ?? auth()->user();
        $freelancerProposals = $this->model->where('freelancer_id', $user->id)->filterByStatus($status)->with('project', 'milestones')
            ->orderBy('created_at', 'desc')->paginate(15);
        return new BaseCollection($freelancerProposals, ProposalResource::class, null);
    }
    public function getProjectProposals(Project $project, string $status)
    {
        $employerProposal = $project->proposals()
            ->filterByStatus($status)->with('milestones')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        // return $employerProposal;
        return new BaseCollection($employerProposal, EmployerProposalsResource::class, null);
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