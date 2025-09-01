<?php

namespace App\Http\Resources\Locale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'province_id' => $this->id,
            'province_name' => $this->name,
            'cities' => [
                $this->cities
                ? $this->cities->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                    ];
                })
                : null,
            ],
        ];
    }
}
