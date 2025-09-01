<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Project;
use App\Models\User\User;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface ProjectRepositoryInterface extends
    ShowableRepositoryInterface,
    CreatableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getProjects(array $data): Paginator;
    public function searchProject(string $search): Paginator;
    public function getUserProjects(?User $user, array $data): Paginator;
    public function showProject(Project $project): Project;
    public function syncSkills(Project $project, array $skills);
}