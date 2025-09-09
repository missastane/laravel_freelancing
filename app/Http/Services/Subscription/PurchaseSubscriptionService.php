<?php

namespace App\Http\Services\Subscription;
use App\Exceptions\Market\NotEnoughBalanceException;
use App\Http\Resources\Market\SubscriptionUserDetailResource;
use App\Models\Market\Subscription;
use App\Models\Market\UserSubscription;
use App\Models\Payment\Wallet;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\Market\UserSubscriptionRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
class PurchaseSubscriptionService
{
    public function __construct(
        // protected SubscriptionRepositoryInterface $subscriptionRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected UserSubscriptionRepositoryInterface $userSubscriptionRepository
    ) {
        
    }

    public function checkWalletBalance(Subscription $subscription)
    {
        if (!$this->walletRepository->hasEnoughBalance(auth()->id(), $subscription->amount)) {
            throw new NotEnoughBalanceException();
        }
    }
    public function renewSubscription(UserSubscription $oldSubscription, Subscription $subscription)
    {
        $this->userSubscriptionRepository->update($oldSubscription, [
            'end_date' => $oldSubscription->end_date->addDays($subscription->duration_days)
        ]);
        $message = "طرح {$subscription->name} تمدید شد و تا تاریخ {$oldSubscription->end_date} معتبر است";
        return [
            'message' => $message,
            'data' => $oldSubscription,
            'code' => 200
        ];
    }

    public function moveSubscriptionToQueue(UserSubscription $oldSubscription, Subscription $subscription, $user)
    {
        $newSubscription = $this->userSubscriptionRepository->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'start_date' => $oldSubscription->end_date,
            'end_date' => $oldSubscription->end_date->addDays($subscription->duration_days),
            'status' => 1
        ]);
        $message = "طرح {$subscription->name} با موفقیت خریداری شد و پس از انقضا طرح قبلی قعال خواهد شد";
        return [
            'message' => $message,
            'data' => $newSubscription,
            'code' => 201
        ];

    }

    public function updateWallet(Wallet $wallet, Subscription $subscription, UserSubscription $newSubscription)
    {
        $this->walletRepository->update($wallet, [
            'balance' => $wallet->balance - $subscription->amount
        ]);
        $this->walletTransactionRepository->create([
            'wallet_id' => $wallet->id,
            'amount' => $subscription->amount,
            'transaction_type' => 6,
            'description' => 'خرید پلن جدید',
            'related_type' => UserSubscription::class,
            'related_id' => $newSubscription->id
        ]);
    }
    public function buySubscription(Subscription $subscription): array
    {
        return DB::transaction(function () use ($subscription) {
            $user = auth()->user();
            $this->checkWalletBalance($subscription);
            $oldSubscription = $this->userSubscriptionRepository->getLastUserSubscription($user->id);
            $result = [];
            if ($oldSubscription) {
                if ($oldSubscription->subscription_id == $subscription->id) {
                    $result = $this->renewSubscription($oldSubscription, $subscription);
                    $newSubscription = $oldSubscription;
                } else {
                    $result = $this->moveSubscriptionToQueue($oldSubscription, $subscription, $user);
                    $newSubscription = $result['data'];
                }
            } else {
                $newSubscription = $this->userSubscriptionRepository->create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'start_date' => now(),
                    'end_date' => now()->addDays($subscription->duration_days),
                    'status' => 2
                ]);
                $message = "طرح {$subscription->name} با موفقیت خریداری شد و تا تاریخ {$newSubscription->end_date} معتبر است";
                $result = [
                    'message' => $message,
                    'data' => $newSubscription,
                    'code' => 201

                ];
            }
            $this->updateWallet($this->walletRepository->findByUserId($user->id), $subscription, $newSubscription);
            $result['data'] = new SubscriptionUserDetailResource($result['data']->load('subscription'));
            return $result;
        });
    }
}