<?php

namespace App\Http\Services\Payment;

use App\Events\AddWithDrawalRequest;
use App\Models\Payment\Withdrawal;
use App\Models\User\User;
use App\Notifications\WithDrawalChangeNotification;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Contracts\Payment\WithdrawalRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class WithDrawalRequestService
{
    public function __construct(
        protected WithdrawalRepositoryInterface $withdrawalRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository
    ) {
    }

    public function getWithdrawalRequests(string $status)
    {
        return $this->withdrawalRepository->getAllByFilter($status);
    }
    public function showRequest(Withdrawal $Withdrawal)
    {
        return $this->withdrawalRepository->showRequest($Withdrawal);
    }
    protected function checkWithDrawalAmount(int $amount)
    {
        $wallet = $this->walletRepository->findByUserId(auth()->id());
        $walletBalance = $wallet->balance;
        $minRemain = 2000; //tooman
        $maxWithdrawable = $walletBalance - $minRemain;
        if ($amount > $maxWithdrawable) {
            return false;
        }
        return true;
    }
    public function storeWithdrawalRequest(array $data)
    {
        $user = auth()->user();
        $this->checkWithDrawalAmount($data['amount']);
        Withdrawal::create([
            'user_id' => $user->id,
            'account_number_sheba' => $data['account_number_sheba'],
            'card_number' => $data['card_number'],
            'bank_name' => $data['bank_name'],
            'amount' => $data['amount'],
        ]);
        event(new AddWithDrawalRequest($user));
    }
    public function changeRequestToPaid(Withdrawal $withdrawal)
    {
        $user = $withdrawal->user;
        $wallet = $user->wallet;
        if ($withdrawal->status === 1) {
            DB::transaction(function () use ($wallet, $withdrawal) {
                $this->withdrawalRepository->update($withdrawal, [
                    'status' => 2,
                    'paid_at' => now()
                ]);
                $this->walletRepository->update($wallet, ['balance' => $wallet->balance - $withdrawal->amount]);
                $this->walletTransactionRepository->create([
                    'wallet_id' => $wallet->id,
                    'amount' => $withdrawal->amount,
                    'transaction_type' => 2,
                    'description' => "مبلغ {$withdrawal->amount} تومان بنا بر درخواست شما به کارت شما واریز شد",
                    'related_type' => Withdrawal::class,
                    'related_id' => $withdrawal->id,
                ]);
            });
            $user->notify(new WithDrawalChangeNotification('درخواست برداشت از کیف پول شما با موفقیت انجام گرفت'));
            return true;
        } else {
            return false;
        }
    }

    public function rejectRequest(Withdrawal $Withdrawal, array $data)
    {
        if ($Withdrawal->status === 1) {
            $this->withdrawalRepository->update($Withdrawal, [
                'status' => 3,
                'rejected_note' => $data['rejected_note']
            ]);
            $user = $Withdrawal->user;
            $user->notify(new WithDrawalChangeNotification('درخواست برداشت از کیف پول شما رد شد'));
            return true;
        } else {
            return false;
        }
    }
}