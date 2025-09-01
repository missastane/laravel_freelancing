<?php

namespace App\Repositories\Contracts\User;

use App\Models\Market\UserEducation;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface UserEducationRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getUserEducations(): Paginator;
    public function showEducation(UserEducation $userEducation);

}