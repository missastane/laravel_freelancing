<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithDrawalRequestResource extends JsonResource
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
            'user'=> [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'national_code' => $this->user->national_code
            ],
            'account_number_sheba' => $this->account_number_sheba,
            'card_number' => $this->card_number,
            'bank_name' => $this->bank_name,
            'amount' => $this->amount,
            'status' =>$this->status_value,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at
        ];
    }
}
