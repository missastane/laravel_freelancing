<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\SubscriptionFeature;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class SubscriptionFeatureRepository extends BaseRepository implements SubscriptionFeatureRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;

    public function __construct(SubscriptionFeature $model)
    {
        parent::__construct($model);
    }
}