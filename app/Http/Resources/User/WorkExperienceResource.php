<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkExperienceResource extends JsonResource
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
            'province' => $this->province->name,
            'company_name' => $this->company_name,
            'position' => $this->position,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year
        ];
    }
}
