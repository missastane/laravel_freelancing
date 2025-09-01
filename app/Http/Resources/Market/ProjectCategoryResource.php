<?php

namespace App\Http\Resources\Market;

use App\Http\Resources\Content\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCategoryResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image['indexArray']['medium'],
            'status' => $this->status_value,
            'show_in_menu' => $this->show_in_menu_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'parent' =>
                $this->parent ? [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name
                ] : null,
            'tags' => TagResource::collection($this->whenLoaded('tags'))
        ];
    }
}
