<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            'value' => $this->value,
            'order_id' => $this->order_id,
            'rate_by' => [
                'username' => $this->user->username,
                'avatar' => $this->user->profile_photo_path
            ],
            'created_at' => $this->created_at
        ];
    }
}
