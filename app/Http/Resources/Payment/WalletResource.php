<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'owner_first_name' => $this->user->first_name,
            'owner_last_name' => $this->user->last_name,
            'owner_national_code' => $this->user->national_code,
            'balance' => $this->balance,
            'locked_balance' => $this->locked_balance,
            'currency' => $this->currency_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
