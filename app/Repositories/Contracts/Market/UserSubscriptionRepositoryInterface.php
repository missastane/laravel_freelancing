<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\UserSubscription;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface UserSubscriptionRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function getLastUserSubscription(int $userId): UserSubscription|null;
}