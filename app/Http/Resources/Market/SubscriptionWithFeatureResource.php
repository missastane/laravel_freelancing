<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionWithFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'duration_days' => $this->duration_days,
            'commission_rate' => $this->commission_rate,
            'max_target_per_month' => $this->max_target_per_month,
            'max_notification_per_month' => $this->max_notification_per_month,
            'max_email_per_month' => $this->max_email_per_month,
            'max_sms_per_month' => $this->max_sms_per_month,
            'max_view_deatils_per_month' => $this->max_view_deatils_per_month,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'features' => $this->features?->map(fn($feature) => [
                'id' => $feature->id,
                'feature_key' => $feature->feature_key,
                'feature_persian_key' => $feature->feature_persian_key,
                'feature_value' => $feature->feature_value,
                'feature_value_type' => $feature->feature_value_type,
                'is_limited' => $feature->is_limited_value
            ]),
        ];
    }
}
