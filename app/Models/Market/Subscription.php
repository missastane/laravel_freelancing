<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Subscription",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="پلن نقره ای"),
 *     @OA\Property(property="amount", type="integer", example=300000),
 *     @OA\Property(property="duration_days", type="integer", example=30),
 *     @OA\Property(property="commission_rate", type="integer", example=30),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 * )
 */
class Subscription extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'amount', 'duration_days', 'commission_rate'];


    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class, 'subscription_id');
    }

    public function defaultFeatures()
    {
        return $this->hasMany(SubscriptionDefaultFeature::class);
    }

    public function getFeature(string $key)
    {
        return optional(
            $this->subscriptionFeatures->firstWhere('feature_key', $key)
        )?->castedValue();
    }
}
