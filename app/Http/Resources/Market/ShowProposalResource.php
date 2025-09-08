<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'project' => [
                'id' => $this['proposal']->project->id,
                'title' => $this['proposal']->project->title,
                'slug' => $this['proposal']->project->slug,
                'description' => $this['proposal']->project->description,
                'duration_time' => $this['proposal']->project->duration_time,
                'amount' => $this['proposal']->project->amount,
                'status' => $this['proposal']->project->status_value,
                'is_full_time' => $this['proposal']->project->is_full_time_value,
                'created_at' => $this['proposal']->project->created_at,
                'updated_at' => $this['proposal']->project->updated_at,
                'employer' => auth()->user()->user_type == 2
                    ? [
                        'id' => $this['proposal']->project->employer->id,
                        'first_name' => $this['proposal']->project->employer->first_name,
                        'last_name' => $this['proposal']->project->employer->last_name,
                    ]
                    : [
                        'username' => $this['proposal']->project->employer->username,
                    ],
            ],
            'proposal' => [
                'id' => $this['proposal']->id,
                'description' => $this['proposal']->description,
                'total_amount' => $this['proposal']->total_amount,
                'due_date' => $this['proposal']->due_date,
                'status' => $this['proposal']->status_value,
                'created_at' => $this['proposal']->created_at,
                'updated_at' => $this['proposal']->updated_at,
                'milestones' => $this['proposal']->milestones?->map(fn($milestone) => [
                    'id' => $milestone->id,
                    'title' => $milestone->title,
                    'description' => $milestone->description,
                    'amount' => $milestone->amount,
                    'duration_time' => $milestone->duration_time,
                    'due_date' => $milestone->due_date
                ])
            ],
            'conversation' => [
                'id' => $this['conversation']->id,
                'employer' => auth()->user()->user_type == 2
                    ? [
                        'id' => $this['conversation']->employer->id,
                        'first_name' => $this['conversation']->employer->first_name,
                        'last_name' => $this['conversation']->employer->last_name,
                    ]
                    : [
                        'username' => $this['conversation']->employer->username,
                    ],
                'freelancer' => auth()->user()->user_type == 2
                    ? [
                        'id' => $this['conversation']->freelancer->id,
                        'first_name' => $this['conversation']->freelancer->first_name,
                        'last_name' => $this['conversation']->freelancer->last_name,
                    ]
                    : [
                        'username' => $this['conversation']->freelancer->username,
                    ],
                'status' => $this['conversation']->status_value
            ]
        ];
    }
}
