<?php

namespace App\Repositories\Contracts\Payment;

use App\Models\Payment\Wallet;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface WalletRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function findByUserId($id);
    public function hasEnoughBalance(int $userId, int $amount): bool;
    public function increamentBalance(Wallet $wallet, int $amount): int;
    public function decreamentBalance(Wallet $wallet, int $amount): int;
    public function decrementLocked(Wallet $wallet, int $amount): int;
}