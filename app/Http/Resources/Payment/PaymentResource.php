<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'user' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'nationale_code' => $this->user->nationale_code
            ],
            'amount' => $this->amount,
            'description' => $this->description,
            'transaction_id' => $this->transaction_id,
            'bank_first_response' => $this->bank_first_response ? [
                'success' => $this->bank_first_response['success'] ?? null,
                'authority' => $this->bank_first_response['authority']?? null,
                'payment_url' => $this->bank_first_response['payment_url']?? null
            ] : null,
            'bank_second_response' => $this->bank_second_response ? [
                'success' => $this->bank_second_response['success']?? null,
                'ref_id' => $this->bank_second_response['ref_id']?? null,
                'card_pan' => $this->bank_second_response['card_pan']?? null,
                'fee' => $this->bank_second_response['fee']?? null,
            ] : null,
            'reference_id' => $this->reference_id,
            'paid_at' => $this->paid_at,
            'status' => $this->status_value,
            'created_at' => $this->created_at,
        ];
    }
}
