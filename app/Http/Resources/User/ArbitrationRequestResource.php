<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArbitrationRequestResource extends JsonResource
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
            'dispute_request' => [
                'employer' => [
                    'username' => $this->disputeRequest->orderItem->order->employer->username
                ],
                'freelancer' => [
                    'username' => $this->disputeRequest->orderItem->order->freelancer->username
                ],
                'plaintiff' => [
                    'username' => $this->disputeRequest->user->username,
                    'role' => $this->disputeRequest->user_type_value,
                ],
                'order_item' => [
                    'title' => $this->disputeRequest->orderItem->milestone->title,
                    'due_date' => $this->disputeRequest->orderItem->due_date,
                    'price' => $this->disputeRequest->orderItem->price,
                    'delivered_at' => $this->disputeRequest->orderItem->delivered_at,
                ],
                'reason' => $this->disputeRequest->reason,
                'created_at' => $this->disputeRequest->created_at,
            ],
            'status' => $this->status_value,
            'freelancer_percent' => $this->freelancer_percent,
            'employer_percent' => $this->employer_percent,
            'result_description' => $this->description,
            'resolved_by' => $this->admin->first_name.' '.$this->admin->last_name,
            'resolved_at' => $this->resolved_at
        ];
    }
}
