<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\SubscriptionUsage;
use App\Repositories\Contracts\Market\SubscriptionDefaultUsageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class SubscriptionDefaultUsageRepository extends BaseRepository implements SubscriptionDefaultUsageRepositoryInterface
{
    use HasCreateTrait;
    use HasDeleteTrait;
    use HasShowTrait;
    use HasUpdateTrait;

    public function __construct(SubscriptionUsage $model)
    {
        parent::__construct($model);
    }

    public function increamentUsage(SubscriptionUsage $subscriptionUsage, string $field)
    {
        return $subscriptionUsage->increment($field);
    }

}