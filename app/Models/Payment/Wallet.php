<?php

namespace App\Models\Payment;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Wallet",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="balance", type="integer", example=300000),
 *     @OA\Property(property="locked_balance", type="integer", example=300000),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="currency_value", type="string", example="ریال"),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 * 
 * )
 */
class Wallet extends Model
{
    use HasFactory;
    protected $fillable = ['balance', 'locked_balance', 'user_id', 'currency'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walletTransaction()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function getCurrencyValueAttribute()
    {
        switch($this->currency)
        {
            case 1:
                $result = 'ریال';
                break;
            case 2:
                $result = 'دلار';
                break;
        }
        return $result;
    }
}
