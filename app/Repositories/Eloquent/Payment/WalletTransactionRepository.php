<?php

namespace App\Repositories\Eloquent\Payment;
;

use App\Http\Resources\Payment\TransactionResource;
use App\Http\Resources\Payment\TransactionUserResource;
use App\Http\Resources\Payment\TransactionWalletResource;
use App\Http\Resources\Payment\WalletResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
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

    public function getAllTransactions(?string $type)
    {
        $transactions = $this->model->filterByType($type)
            ->with('wallet')
            ->latest()
            ->paginate(15);
        return new BaseCollection($transactions, TransactionResource::class, null);
    }

    public function getUserWalletTransactions(?User $user = null, ?string $type)
    {
        $user = $user ?? auth()->user();

        $transactions = $this->model
            ->where('wallet_id', $user->wallet->id)
            ->filterByType($type)
            ->with('wallet')
            ->latest()
            ->paginate(15);
        return new BaseCollection($transactions, TransactionResource::class, null);
    }


    public function showTransaction(WalletTransaction $walletTransaction): WalletTransaction
    {
        return $this->showWithRelations($walletTransaction, ['user:id,username']);
    }



}