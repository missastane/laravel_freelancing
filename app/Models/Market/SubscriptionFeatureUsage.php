<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class SubscriptionFeatureUsage extends Model
{
    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'subscription_feature_id',
        'used_count',
        'period_start',
        'period_end'
    ];
    protected function casts()
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function userSubscription()
    {
        return $this->belongsTo(UserSubscription::class,'user_subscription_id');
    }
}
