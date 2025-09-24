<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender' => $this->sender->username,
            'message' => $this->message,
            'files' => $this->files?->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->file_name,
                'path' => $file->file_path,
                'size' => $file->file_size,
                'download_url' => route('file.download', ['file' => $file]),
            ]),
            'sent_date' => $this->sent_date,
            'parent' => $this->parent ? [
                'id' => $this->parent_id,
                'sender' => $this->parent->sender->username,
                'message' => $this->parent->message,
                'files' => $this->files?->map(fn($file) => [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'path' => $file->file_path,
                    'size' => $file->file_size,
                    'download_url' => route('file.download', ['file' => $file]),
                ]),
                'sent_date' => $this->parent->sent_date
            ] : null,
        ];
    }
}
