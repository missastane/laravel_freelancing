<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'persian_title' => $this->persian_title,
            'original_title' => $this->original_title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}