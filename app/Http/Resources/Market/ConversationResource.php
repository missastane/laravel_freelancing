<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'employer' => $this->employer->username,
            'freelancer' => $this->freelancer->username,
            'status' => $this->status_value,
            'messages' => MessageResource::collection($this->whenLoaded('messages'))
        ];
    }
}
