<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'type' => $this->transaction_type_value,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'wallet_balance' => $this->wallet->balance,
            'wallet_locked_balance' => $this->wallet->locked_balance,
            'wallet_currency' => $this->wallet->currency_value
        ];
    }
}
