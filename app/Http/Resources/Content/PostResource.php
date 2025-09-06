<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'summary' => $this->summary,
            'content' => $this->content,
            'image' => $this->image['indexArray']['medium'],
            'study_time' => $this->study_time,
            'view' => $this->view,
            'status' => $this->status_value,
            'related_posts' => $this->related_posts_value?->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
            ]),
            'post_category' => [
                'id' => $this->postCategory->id,
                'name' => $this->postCategory->name,
            ],
            'author' => [
                'id' => $this->author->id,
                'first_name' => $this->author->first_name,
                'last_name' => $this->author->last_name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'files' => $this->files?->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->file_name,
                'path' => $file->file_path,
                'size' => $file->file_size,
            ]),
        ];
    }
}
