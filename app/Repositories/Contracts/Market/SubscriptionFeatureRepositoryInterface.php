<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\SubscriptionFeature;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

interface SubscriptionFeatureRepositoryInterface extends
    ShowableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values);
}