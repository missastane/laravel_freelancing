<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\SubscriptionUserDetailResource;
use App\Http\Resources\Market\SubscriptionWithFeatureResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Market\Subscription;
use App\Models\Market\SubscriptionFeature;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    public function getAllSubscriptions()
    {
        $subscriptions = $this->all();
        return new BaseCollection($subscriptions, SubscriptionWithFeatureResource::class,null);
    }

    public function getAllowedSubscriptionPlans()
    {
        $user = auth()->user();
        $subscriptions = $this->model->with('features')
            ->orderBy('amount', 'desc')->paginate(5);
        return new BaseCollection($subscriptions, SubscriptionWithFeatureResource::class,null);
    }

    public function showSubscription(Subscription $subscription)
    {
        $result = $this->showWithRelations($subscription, ['features']);
        return new SubscriptionWithFeatureResource($result);
    }
    public function userActivePlan()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription();
        return new SubscriptionUserDetailResource($subscription->load('subscription'));
    }

    public function firstOrCreate(array $attributes, array $values)
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        $record = $this->model->updateOrCreate($attributes, $values);
        return $record;
    }


}