<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Subscription;
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

    public function getAllSubscriptions(): Paginator
    {
        $subscriptions = $this->all();
        return $subscriptions;
    }

    public function getAllowedSubscriptionPlans(): Paginator
    {
        $user = auth()->user();
        $query = match ($user->active_role) {
            'freelancer' => $this->model->where('user_type', 2),
            'employer' => $this->model->where('user_type', 1)
        };
        $subscriptions = $query->with('subscriptionFeatures')
            ->orderBy('amount', 'desc')->simplePaginate(5);
        return $subscriptions;
    }

    public function userActivePlan()
    {
        $user = auth()->user();
        $role = '';
        $subscription = $user->activeSubscription($role);
        $role = $user->active_role === 'freelancer' ? 'freelancer' : 'employer';
        return $subscription;
    }

}