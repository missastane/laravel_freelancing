<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployerProposalsResource extends JsonResource
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
            'freelancer' => auth()->user()->user_type == 2
                ? [
                    'id' => $this->freelancer->id,
                    'first_name' => $this->freelancer->first_name,
                    'last_name' => $this->freelancer->last_name,
                ]
                : [
                    'username' => $this->freelancer->username,
                ],
            'description' => $this->description,
            'total_amount' => $this->total_amount,
            'due_date' => $this->due_date,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'milsstones' => ProposalMilestoneResource::collection($this->whenLoaded('milestones'))
        ];
    }
}
