<?php

namespace App\Http\Services\Payment;

use App\Models\Market\OrderItem;
use App\Models\Payment\Wallet;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;

class WalletService
{
    public function __construct(
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletRepositoryInterface $walletRepository
        ){}
    public function createTransaction(
        Wallet $wallet,
        int $amount,
        int $type,
        string $description,
        string $relatedType,
        int $relatedId
    ) {
        return $this->walletTransactionRepository->create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'transaction_type' => $type,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }

    public function transferFromLockedToBalance(Wallet $from, Wallet $to, int $amount, string $desc, $relatedId)
    {
        $this->walletRepository->decrementLocked($from, $amount);
        $this->walletRepository->increamentBalance($to, $amount);
        $this->createTransaction($from, $amount, 4, $desc, OrderItem::class, $relatedId); // خرج شده
        $this->createTransaction($to, $amount, 4, $desc, OrderItem::class, $relatedId);   // دریافت شده
    }

    public function showWallet()
    {
        return $this->walletRepository->findByUserId(auth()->id());
    }
}
