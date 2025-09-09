<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\UserSubscription;
use App\Repositories\Contracts\Market\UserSubscriptionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class UserSubscriptionRepository extends BaseRepository implements UserSubscriptionRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;

    public function __construct(UserSubscription $model)
    {
        parent::__construct($model);
    }

    public function getLastUserSubscription(int $userId): UserSubscription|null
    {
        $userSubscription = $this->model->where(['user_id' => $userId, 'status' => 2])
            ->where('end_date', '>', now())->first();
        return $userSubscription;
    }

}