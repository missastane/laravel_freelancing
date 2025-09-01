<?php

namespace App\Repositories\Contracts\Payment;

use App\Models\Payment\Withdrawal;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface WithdrawalRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function getAllByFilter(array $data): Paginator;
    public function showRequest(Withdrawal $withdrawal);
}