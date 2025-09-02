<?php

namespace App\Http\Services\Subscription;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\Subscription;
use App\Models\Market\SubscriptionDefaultFeature;
use App\Models\Market\SubscriptionFeature;
use App\Repositories\Contracts\Market\SubscriptionDefaultFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        protected PurchaseSubscriptionService $purchaseSubscriptionService,
        protected SubscriptionRepositoryInterface $subscriptionRepository,
        protected SubscriptionFeatureRepositoryInterface $subscriptionFeatureRepository
    ) {
    }
    public function getAllSubscriptions(): Paginator
    {
        $subscriptions = $this->subscriptionRepository->getAllSubscriptions();
        return $subscriptions;
    }
    public function getAllowedSubscriptionPlans(): Paginator
    {
        return $this->subscriptionRepository->getAllowedSubscriptionPlans();
    }

    public function userActivePlan()
    {
        return $this->subscriptionRepository->userActivePlan();
    }

    public function purchaseSubscription(Subscription $subscription)
    {
        return $this->purchaseSubscriptionService->buySubscription($subscription);
    }
    public function showSubscription(Subscription $subscription)
    {
        return $this->subscriptionRepository->showSubscription($subscription);
    }

    public function storeSubscription(array $data)
    {
        return DB::transaction(function () use ($data) {
            $subscription = $this->subscriptionRepository->updateOrCreate([
                'name' => $data['name'],
                'target_type' => $data['target_type'] == 1 ? Project::class : Proposal::class,
            ], [
                'amount' => $data['amount'],
                'duration_days' => $data['duration_days'],
                'commission_rate' => $data['commission_rate'],
                'max_target_per_month' => $data['max_target_per_month'],
                'max_notification_per_month' => $data['max_notification_per_month'],
                'max_email_per_month' => $data['max_email_per_month'],
                'max_sms_per_month' => $data['max_sms_per_month'],
                'max_view_deatils_per_month' => $data['max_view_deatils_per_month']
            ]);
            if (!empty($data['features'])) {
                foreach ($data['features'] as $feature) {
                    $this->subscriptionFeatureRepository->updateOrCreate(
                        [
                            'subscription_id' => $subscription->id,
                            'feature_key' => $feature['feature_key'],
                        ],
                        [
                            'feature_persian_key' => $feature['feature_persian_key'],
                            'feature_value' => $feature['feature_value'],
                            'feature_value_type' => $feature['feature_value_type'],
                            'is_limited' => $feature['is_limited'] ?? null,
                        ]
                    );
                }
            }
            return $subscription;
        });
    }

    public function updateSubscription(Subscription $subscription, array $data)
    {
        return $this->subscriptionRepository->update($subscription, $data);
    }

    public function deleteFeature(SubscriptionFeature $subscritptionFeature)
    {
        return $this->subscriptionFeatureRepository->delete($subscritptionFeature);
    }
    public function deleteSubscription(Subscription $subscription)
    {
        return $this->subscriptionRepository->delete($subscription);
    }

}