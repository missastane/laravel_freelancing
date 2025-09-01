<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_id', 'feature_key', 'persian_feature_key', 'feature_value', 'feature_value_type','is_limited'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function castedValue()
    {
        return match ($this->value_type) {
            'integer' => (int) $this->feature_value,
            'boolean' => filter_var($this->feature_value, FILTER_VALIDATE_BOOLEAN),
            default => $this->feature_value,
        };
    }

}
