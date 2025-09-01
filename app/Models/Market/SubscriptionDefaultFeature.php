<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

class SubscriptionDefaultFeature extends Model
{
    protected $fillable = [
        'subscription_id',
        'target_type',
        'max_target_per_month',
        'max_notification_per_month',
        'max_email_per_month',
        'max_sms_per_month',
        'max_view_deatils_per_month'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class,'subscription_id');
    }
}
