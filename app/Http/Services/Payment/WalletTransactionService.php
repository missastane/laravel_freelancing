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

    public function getAllTransactions(string|null $type)
    {
        return $this->walletTransactionRepository->getAllTransactions($type);
    }
    public function getUserWalletTransactions(?User $user, string|null $type)
    {
        return $this->walletTransactionRepository->getUserWalletTransactions($user, $type);
    }

    public function showTransaction(WalletTransaction $walletTransaction)
    {
        return $this->walletTransactionRepository->showTransaction($walletTransaction);
    }
}