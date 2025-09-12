<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'banner' => $this->banner,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
             'files' => $this->files?->map(fn($file) => [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_path' => $file->file_path,
                    'mime_type' => $file->mime_type,
                ]),
                 'skills' => $this->skills?->map(fn($skill) => [
                    'id' => $skill->id,
                    'title' => $skill->persian_title,
                ]),
        ];
    }
}
