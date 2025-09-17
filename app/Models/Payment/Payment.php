<?php

namespace App\Models\Payment;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *                  @OA\Property(property="national_code", type="string", example="27301436589"),
 *               )
 *            ),
 *     @OA\Property(property="amount", type="float", example=5000.000),
 *     @OA\Property(property="description", type="string", example="for wallet charge"),
 *     @OA\Property(property="transaction_id", type="string", example="S000000000000000000000000000000w758z"),
 *     @OA\Property(property="bank_first_response", type="array", 
 *         @OA\Items(
 *            @OA\Property(property="success",type="boolean", example=true),
 *            @OA\Property(property="authority",type="string", example="S000000000000000000000000000000w758z"),
 *            @OA\Property(property="payment_url",type="string", example="https:\/\/www.zarinpal.com\/pg\/StartPay\/S000000000000000000000000000000w758z")
 *          )
 *     ),
 *     @OA\Property(property="bank_second_response", type="array", 
 *         @OA\Items(
 *            @OA\Property(property="success",type="boolean", example=true),
 *            @OA\Property(property="ref_id",type="string", example="1359401"),
 *            @OA\Property(property="card_pan",type="string", example="999999******9999"),
 *            @OA\Property(property="fee",type="integer", example=100000)
 *          )
 *     ),
 *     @OA\Property(property="reference_id", type="string", example=1359401),
 *     @OA\Property(property="paid_at", type="string", format="date-time", description="paid at datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="status", type="string", description="Payment status: 'pending' if 1, 'paid' if 2, 'not-paid' if 3, 'returned' if 4", example="پرداخت شده"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount','description', 'gateway', 'transaction_id', 'bank_first_response', 'bank_second_response', 'reference_id', 'paid_at', 'status'];

    protected function casts()
    {
        return [
            'paid_at' => 'datetime',
            'bank_first_response' => 'array',
            'bank_second_response' => 'array'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در انتظار پرداخت';
                break;
            case 2:
                $result = 'پرداخت شده';
                break;
            case 3:
                $result = 'پرداخت نشده';
                break;
            case 4:
                $result = 'عودت وجه';
                break;
        }
        return $result;
    }

    public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $paymentStatuses = [
            'pending' => 1,
            'paid' => 2,
            'not-paid' => 3,
            'returned' => 4
        ];

        // if the type is valid filters query
        if (isset($paymentStatuses[$status])) {
            return $query->where('status', $paymentStatuses[$status]);
        }
        // return all the payments
        return $query;
    }
}
