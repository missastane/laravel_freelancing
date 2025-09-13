<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender' => $this->sender->username,
            'message' => $this->message,
            'sent_date' => $this->sent_date,
            'parent' => $this->parent ? [
                'id' => $this->parent_id,
                'sender' => $this->parent->sender->username,
                'message' => $this->parent->message,
                'sent_date' => $this->parent->sent_date
            ] : null,
        ];
    }
}
