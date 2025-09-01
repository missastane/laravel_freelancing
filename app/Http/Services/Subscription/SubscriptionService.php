<?php

namespace App\Http\Services\Subscription;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\Subscription;
use App\Models\Market\SubscriptionDefaultFeature;
use App\Models\Market\SubscriptionFeature;
use App\Models\Market\SubscritptionFeature;
use App\Models\User\User;
use App\Repositories\Contracts\Market\SubscriptionDefaultFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        protected PurchaseSubscriptionService $purchaseSubscriptionService,
        protected SubscriptionRepositoryInterface $subscriptionRepository,
        protected SubscriptionDefaultFeatureRepositoryInterface $subscriptionDefaultFeatureRepository,
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
        return $this->subscriptionRepository->showWithRelations($subscription);
    }

    public function storeSubscription(array $data)
    {
        return DB::transaction(function () use ($data) {
            $subscription = $this->subscriptionRepository->create([
                'name' => $data['name'],
                'amount' => $data['amount'],
                'duration_days' => $data['duration_days']
            ]);
            $subscriptionDeafultFeatures = $this->subscriptionDefaultFeatureRepository->create([
                'subscription_id' => $subscription->id,
                'target_type' => $data['target_type'] == 1 ? Project::class : Proposal::class,
                'max_target_per_month' => $data['max_target_per_month'],
                'max_notification_per_month' => $data['max_notification_per_month'],
                'max_email_per_month' => $data['max_email_per_month'],
                'max_sms_per_month' => $data['max_sms_per_month'],
                'max_view_deatils_per_month' => $data['max_view_deatils_per_month']
            ]);
            if ($data['features']) {
                foreach ($data['features'] as $feature) {
                    $this->subscriptionFeatureRepository->create([
                        'subscription_id' => $subscription->id,
                        'target_type' => $data['target_type'] == 1 ? Project::class : Proposal::class,
                        'feature_key' => $feature['feature_key'],
                        'feature_value' => $feature['feature_value'],
                        'feature_value_type' => $feature['feature_value_type']
                    ]);
                }
            }
            return $subscription;
        });
    }

    public function updateSubscription(Subscription $subscription, array $data)
    {
        return $this->subscriptionRepository->update($subscription, $data);
    }

    public function updateDefaultFeature(SubscriptionDefaultFeature $subscriptionDefaultFeature, array $data)
    {
        return $this->subscriptionDefaultFeatureRepository->update($subscriptionDefaultFeature,$data);
    }

    public function updateFeature(SubscriptionFeature $subscritptionFeature, array $data)
    {
        return $this->subscriptionFeatureRepository->update($subscritptionFeature,$data);
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