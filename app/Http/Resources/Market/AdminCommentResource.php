<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCommentResource extends JsonResource
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
            'user' => $this->user->username,
            'comment' => $this->comment,
            'seen' => $this->seen_value,
            'approved' => $this->approved_value,
            'status' => $this->status_value,
            'commentable_type' => $this->commentable_type_value,
            'commentable_id' => $this->commentable_id,
            'parent' => $this->parent ? [
                'user' => $this->parent->user->username,
                'comment' => $this->parent->comment,
                'created_at' => $this->parent->created_at
            ] : null,
            'created_at' => $this->created_at
        ];
    }
}
