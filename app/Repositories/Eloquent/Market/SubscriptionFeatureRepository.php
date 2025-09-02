<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\SubscriptionFeature;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;

class SubscriptionFeatureRepository extends BaseRepository implements SubscriptionFeatureRepositoryInterface
{
    use HasShowTrait;
    use HasDeleteTrait;

    public function __construct(SubscriptionFeature $model)
    {
        parent::__construct($model);
    }

     public function updateOrCreate(array $attributes, array $values)
    {
        $record = $this->model->updateOrCreate($attributes, $values);
        return $record;
    }
}