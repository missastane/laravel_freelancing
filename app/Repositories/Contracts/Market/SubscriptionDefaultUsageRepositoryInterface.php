<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\SubscriptionUsage;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface SubscriptionDefaultUsageRepositoryInterface extends
    CreatableRepositoryInterface,
    UpdatableRepositoryInterface,
    ShowableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function increamentUsage(SubscriptionUsage $subscriptionUsage,string $field);
}