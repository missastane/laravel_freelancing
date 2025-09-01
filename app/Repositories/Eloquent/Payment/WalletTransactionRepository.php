<?php

namespace App\Repositories\Eloquent\Payment;
;

use App\Models\Payment\WalletTransaction;
use App\Models\User\User;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use Illuminate\Contracts\Pagination\Paginator;

class WalletTransactionRepository extends BaseRepository implements WalletTransactionRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    public function __construct(WalletTransaction $model)
    {
        parent::__construct($model);
    }

    public function getAllTransactions(array $data): Paginator
    {
        $transactions = WalletTransaction::filterByType($data)
            ->orderBy('created_at', 'desc')
            ->with('user:id,first_name,last_name,national_code')->simplePaginate(15);
        return $transactions;
    }

    public function getUserWalletTransactions(?User $user = null, array $data): Paginator
    {
        $user = $user ? $user : auth()->user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->with('user:id,first_name,last_name,national_code')
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return $transactions;
    }

    public function showTransaction(WalletTransaction $walletTransaction): WalletTransaction
    {
        return $this->showWithRelations($walletTransaction,['user:id,username']);
    }



}