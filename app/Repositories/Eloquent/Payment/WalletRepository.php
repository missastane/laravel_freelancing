<?php

namespace App\Repositories\Eloquent\Payment;

use App\Models\Payment\Wallet;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class WalletRepository extends BaseRepository implements WalletRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    public function __construct(Wallet $model)
    {
        parent::__construct($model);
    }

    public function findByUserId($id): Wallet
    {
        $wallet = Wallet::where('user_id', $id)->first();
        return $wallet;
    }

    public function hasEnoughBalance(int $userId, int $amount): bool
    {
        $wallet = $this->findByUserId($userId);
        $balance = $wallet->balance;
        $lockedBalance = $wallet->locked_balance;
        if ($balance - $lockedBalance < $amount) {
            return false;
        }
        return true;
    }

    public function increamentBalance(Wallet $wallet, int $amount): int
    {
        return $wallet->increment('balance', $amount);
    }

    public function decreamentBalance(Wallet $wallet, int $amount): int
    {
        return $wallet->decrement('balance', $amount);
    }

    public function decrementLocked(Wallet $wallet, int $amount): int
    {
        return $wallet->decrement('locked_balanced', $amount);
    }

}