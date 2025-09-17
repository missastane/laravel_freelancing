<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Market\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteProjectResource extends JsonResource
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
            'favoritable' => new ProjectResource($this->favoritable),
            'created_at' => $this->created_at
        ];

    }
}
