<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\UserSubscription;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface UserSubscriptionRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface
{
    public function getLastUserSubscription(int $userId): UserSubscription;
}