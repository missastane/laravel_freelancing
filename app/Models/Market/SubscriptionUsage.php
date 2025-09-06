<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class SubscriptionUsage extends Model
{
    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'target_create_count',
        'send_notification_count',
        'send_email_count',
        'send_sms_count',
        'view_details_count',
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

    public function userSubscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

   
}
