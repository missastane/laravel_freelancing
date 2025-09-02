<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SubscriptionFeature",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="feature_key", type="string", example="vip-proposal"),
 *     @OA\Property(property="feature_persian_key", type="string", example="پیشنهاد اختصاصی"),
 *     @OA\Property(property="feature_value", type="string", example="10"),
 *     @OA\Property(property="feature_value_type", type="string", example="عدد"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="is_limited_value", type="string", description="1 => yes, 2 => no", example="بله")
 * )
 */
class SubscriptionFeature extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_id', 'feature_key', 'feature_persian_key', 'feature_value', 'feature_value_type', 'is_limited'];

    
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

    public function getIsLimitedValueAttribute()
    {
        if ($this->is_limited == 1) {
            return 'بله';
        } else {
            return 'نه';
        }
    }

}
