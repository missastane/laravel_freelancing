<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'project' => [
                'employer' => $this->employer->username,
                'title' => $this->project->title,
                'slug' => $this->project->slug,
                'description' => $this->project->description,
                'duration_time' => $this->project->duration_time,
                'amount' => $this->project->amount
            ],
            'proposal' => [
                'freelancer' => $this->freelancer->username,
                'description' => $this->proposal->description,
                'total_amount' => $this->proposal->total_amount,
                'due_date' => $this->proposal->due_date
            ],
            'orderItems' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'total_price' => $this->total_price,
            'due_date' => $this->due_date,
            'delivered_at' => $this->delivered_at,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'comments' => CommentResource::collection($this->whenLoaded('comments'))
        ];
    }
}
