<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration_time' => $this->duration_time,
            'amount' => $this->amount,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'employer' => auth()->user()->user_type == 2
                ? [
                    'id' => $this->employer->id,
                    'first_name' => $this->employer->first_name,
                    'last_name' => $this->employer->last_name,
                ]
                : [
                    'username' => $this->employer->username,
                ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ],
            'files' => $this->files?->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->file_name,
                'path' => $file->file_path,
                'mime_type' => $file->mime_type,
            ]),
            'skills' => $this->skills?->map(fn($skill) => [
                'id' => $skill->id,
                'persian_title' => $skill->persian_title,
                'original_title' => $skill->original_title,
            ]),
        ];
    }
}
