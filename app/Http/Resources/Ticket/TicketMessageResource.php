<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
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
                'author' => $this->author->username,
                'author_role' => $this->author_type_value,
                'message' => $this->message,
                'parent' => $this->parent ? [
                    'id' => $this->parent->id,
                    'message' => $this->parent->message,
                    'files' => $this->parent->files?->map(fn($file) => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_path' => $file->file_path,
                        'mime_type' => $file->mime_type,
                    ]),
                ] : null,
                'files' => $this->files?->map(fn($file) => [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_path' => $file->file_path,
                    'mime_type' => $file->mime_type,
                ]),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
        ];
    }
}
