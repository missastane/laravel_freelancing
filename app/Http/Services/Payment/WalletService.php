<?php

namespace App\Http\Services\Payment;

use App\Http\Resources\Payment\WalletResource;
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

    public function transferFromLockedToBalance(Wallet $from, Wallet $to, int $itemPrice,int $freelancerAmount, string $desc, $relatedId)
    {
        $this->walletRepository->decreamentBalance($from,$itemPrice);
        $this->walletRepository->decrementLocked($from, $itemPrice);
        $this->walletRepository->increamentBalance($to, $freelancerAmount);
        $this->createTransaction($from, $itemPrice, 4, $desc, OrderItem::class, $relatedId); // خرج شده
        $this->createTransaction($to, $freelancerAmount, 4, $desc, OrderItem::class, $relatedId);   // دریافت شده
    }

    public function showWallet()
    {
        $result = $this->walletRepository->findByUserId(auth()->id());
        return new WalletResource($result);
    }
}
