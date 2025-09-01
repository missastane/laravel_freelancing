<?php

namespace App\Repositories\Contracts\Payment;

use App\Models\Payment\WalletTransaction;
use App\Models\User\User;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ListableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface WalletTransactionRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface
{
    public function getAllTransactions(array $data): Paginator;
    public function getUserWalletTransactions(?User $user = null, array $data): Paginator;
    public function showTransaction(WalletTransaction $walletTransaction): WalletTransaction;
}