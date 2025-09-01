<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\DisputeRequest;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ListableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface DisputeRequestRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getAllByFilter(array $data): Paginator;
    public function getUserRequests(): Paginator;
    public function showDisputRequest(DisputeRequest $disputeRequest);
}