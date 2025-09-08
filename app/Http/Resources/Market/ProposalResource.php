<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
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
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'project_price' => $this->project->amount,
            'project_days' => $this->project->duration_time,
            'description' => $this->description,
            'total_amount' => $this->total_amount,
            'due_date' => $this->due_date,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'milestones' => ProposalMilestoneResource::collection($this->whenLoaded('milestones'))
        ];
    }
}
