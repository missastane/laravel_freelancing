<?php

namespace App\Models\Payment;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="WithdrawalRequest",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="account_number_sheba", type="string", example="IR12 1245 1258 6985 5789 2456 34"),
 *     @OA\Property(property="card_number", type="string", example="6037227458963256"),
 *     @OA\Property(property="bank_name", type="string", example="ملی"),
 *     @OA\Property(property="amount", type="integer", example=800000),
 *     @OA\Property(property="paid_at", type="string", format="date-time", description="pay datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Status: 1=> pending, 2 => accepted, 3 => rejected", example="در حال بررسی"),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 * )
 */
class Withdrawal extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'account_number_shaba', 'card_number', 'bank_name', 'amount', 'status', 'paid_at'];

    protected function casts()
    {
        return ['paid_at' => 'datetime'];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'معلق';
                break;
            case 2:
                $result = 'پذیرفته شده';
                break;
            case 3:
                $result = 'رد شده';
                break;
        }
        return $result;
    }

      public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $requestStatuses = [
            'pending' => 1,
            'accepted' => 2,
            'rejected' => 3
        ];

        // if the type is valid filters query
        if (isset($requestStatuses[$status])) {
            return $query->where('status', $requestStatuses[$status]);
        }
        // return all the requests
        return $query;
    }
}
