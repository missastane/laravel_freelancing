<?php

namespace App\Http\Services\Payment;

use App\Events\AddWithDrawalRequest;
use App\Models\Payment\Withdrawal;
use App\Models\User\User;
use App\Notifications\WithDrawalChangeNotification;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WithdrawalRepositoryInterface;
use App\Traits\ApiResponseTrait;

class WithDrawalRequestService
{
    public function __construct(
        protected WithdrawalRepositoryInterface $withdrawalRepository,
        protected WalletRepositoryInterface $walletRepository
    ) {
    }

    public function getWithdrawalRequests(array $data)
    {
        return $this->withdrawalRepository->getAllByFilter($data);
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
        $data['user_id'] = $user->id;
        Withdrawal::create($data);
        event(new AddWithDrawalRequest($user));
    }
    public function changeRequestToPaid(Withdrawal $Withdrawal)
    {
        if ($Withdrawal->status === 1) {
            $this->withdrawalRepository->update($Withdrawal, [
                'status' => 2,
                'paid_at' => now()
            ]);
            $user = $Withdrawal->user;
            $user->notify(new WithDrawalChangeNotification('درخواست برداشت از کیف پول شما با موفقیت انجام گرفت'));
            return true;
        } else {
            return false;
        }
    }

    public function rejectRequest(Withdrawal $Withdrawal)
    {
        if ($Withdrawal->status === 1) {
            $this->withdrawalRepository->update($Withdrawal, [
                'status' => 3
            ]);
            $user = $Withdrawal->user;
            $user->notify(new WithDrawalChangeNotification('درخواست برداشت از کیف پول شما رد شد'));
            return true;
        } else {
            return false;
        }
    }
}