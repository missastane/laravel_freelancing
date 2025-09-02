<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Subscription;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface SubscriptionRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllSubscriptions(): Paginator;
    public function getAllowedSubscriptionPlans(): Paginator;
    public function userActivePlan();
    public function firstOrCreate(array $attributes, array $values);
    public function showSubscription(Subscription $subscription);
    public function updateOrCreate(array $attributes, array $values);


}