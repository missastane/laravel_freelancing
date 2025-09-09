<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketWithMessagesResource extends JsonResource
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
            'priority' => $this->priority->name,
            'department' => $this->department->name,
            'dispute_request' => $this->disputeRequest ? $this->disputeRequest->reason : null,
            'ticket_type' => $this->ticket_type_value,
            'subject' => $this->subject,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'messages' => TicketMessageResource::collection($this->whenLoaded('ticketMessages'))
        ];
    }
}
