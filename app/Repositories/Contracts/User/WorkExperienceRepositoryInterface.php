<?php

namespace App\Repositories\Contracts\User;

use App\Models\Market\WorkExperience;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface WorkExperienceRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    DeletableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function getUserExperiences();
     public function showExperience(WorkExperience $workExperience);
}