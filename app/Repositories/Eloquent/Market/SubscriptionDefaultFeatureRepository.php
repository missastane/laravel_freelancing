<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\SubscriptionDefaultFeature;
use App\Repositories\Contracts\Market\SubscriptionDefaultFeatureRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class SubscriptionDefaultFeatureRepository extends BaseRepository implements SubscriptionDefaultFeatureRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;

    public function __construct(SubscriptionDefaultFeature $model)
    {
        parent::__construct($model);
    }
}