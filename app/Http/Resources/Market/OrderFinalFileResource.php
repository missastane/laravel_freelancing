<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderFinalFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'files' => $this->finalFiles?->map(fn($file) => [
                'file_name' => $file->file->file_name,
                'path_path' => $file->file->file_path,
                'mime_type' => $file->file->mime_type,
                'file_size' => $file->file->file_size,
                'download_url' => route('file.download', ['file' => $file->file]),
            ]),

        ];
    }
}
