<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'amount',
        'duration_days',
        'commission_rate',
        'max_target_per_month',
        'max_notification_per_month',
        'max_email_per_month',
        'max_sms_per_month',
        'max_view_deatils_per_month'
    ];


    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class, 'subscription_id');
    }


    public function getFeature(string $key)
    {
        return optional(
            $this->subscriptionFeatures->firstWhere('feature_key', $key)
        )?->castedValue();
    }

     public function scopeSearch(Builder $query, null|string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        });
    }
}
