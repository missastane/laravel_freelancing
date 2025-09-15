<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeRequestResource extends JsonResource
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
            'employer' => [
                'username' => $this->orderItem->order->employer->username
            ],
            'freelancer' => [
                'username' => $this->orderItem->order->freelancer->username
            ],
            'order_item' => [
                'id' => $this->order_item_id,
                'title' => $this->orderItem->milestone->title,
                'due_date' => $this->orderItem->due_date,
                'price' => $this->orderItem->price,
                'order_id' => $this->orderItem->order_id
            ],
            'final_file' => [
                'id' => $this->finalFile->id,
                'file_name' => $this->finalFile->file->file_name,
                'file_path' => $this->finalFile->file->file_path,
                'delivered_at' => $this->finalFile->delivered_at
            ],
            'plaintiff' => [
                'username' => $this->user->username,
                'role' => $this->user_type_value,
            ],
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
