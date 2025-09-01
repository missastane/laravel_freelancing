<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = ['user_id', 'subscription_id', 'start_date', 'end_date', 'status'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function getFeature(string $key)
    {
        return $this->subscription?->getFeature($key);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function defaultUsage()
    {
        return $this->hasOne(SubscriptionDefaultUsage::class,'user_subscription_id');
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال انتظار';
                break;
            case 2:
                $result = 'فعال';
                break;
            case 3:
                $result = 'منقضی شده';
                break;
            case 4:
                $result = 'لغو شده';
                break;
        }
        return $result;
    }

    public function getSmsLimit():int
    {
        return $this->defaultUsage->send_sms_count;
    }

    public function getEmailLimit()
    {
        return $this->defaultUsage->send_email_count;
    }

    public function getNotificationLimit()
    {
         return $this->defaultUsage->send_notification_count;
    }
    
    public function getTargetCreateLimit()
    {
        return $this->defaultUsage->target_create_count;
    }

    public function getViewDetailsLimit()
    {
        return $this->defaultUsage->view_details_count;
    }
}
