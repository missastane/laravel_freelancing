<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEducationResource extends JsonResource
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
            'university_name' => $this->university_name,
            'field_of_study' => $this->field_of_study,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year
        ];
    }
}
