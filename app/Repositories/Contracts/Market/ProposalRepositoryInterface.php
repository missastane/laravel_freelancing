<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface ProposalRepositoryInterface extends
    ShowableRepositoryInterface,
    CreatableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
     public function existsForProjectAndFreelancer($projectId, $freelancerId): bool;
    public function getProposals(?string $status);
     public function getProjectProposals(Project $project, ?string $status);
    public function showProposal(Proposal $proposal): Proposal;
    public function updateWhere(array $conditions, array $data): int;
    public function getProjectProposalsStats(Project $project): array;


}