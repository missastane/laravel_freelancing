<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'title' => $this->milestone->title,
            'description' => $this->milestone->description,
            'price' => $this->price,
            'freelancer_amount' => $this->freelancer_amount,
            'platform_fee' => $this->platform_fee,
            'due_date' => $this->due_date,
            'delivered_at' => $this->delivered_at
        ];
    }
}
