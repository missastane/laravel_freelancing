<?php

namespace App\Http\Services\Payment;

use App\Models\Payment\WalletTransaction;
use App\Models\User\User;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class WalletTransactionService
{
    public function __construct(
        protected WalletTransactionRepositoryInterface $walletTransactionRepository
    ) {
    }

    public function getAllTransactions(array $data): Paginator
    {
        return $this->walletTransactionRepository->getAllTransactions($data);
    }
    public function getUserWalletTransactions(?User $user, array $data): Paginator
    {
        return $this->walletTransactionRepository->getUserWalletTransactions($user, $data);
    }

    public function showTransaction(WalletTransaction $walletTransaction): WalletTransaction
    {
        return $this->walletTransactionRepository->showTransaction($walletTransaction);
    }
}