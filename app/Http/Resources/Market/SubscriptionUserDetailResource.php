<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionUserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status_value,
            'activePlan' => new SubscriptionWithFeatureResource($this->whenLoaded('subscription'))
        ];
    }
}
