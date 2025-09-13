<?php

namespace App\Models\Payment;

use App\Models\Market\OrderItem;
use App\Models\Market\Subscription;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="WalletTransaction",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="amount", type="integer", example=300000),
 *     @OA\Property(property="type", type="string", description="related type:it maybe one of these: Payment::class, OrderItem::class, Subscription::class", example="پلن اشتراک جدید"),
 *     @OA\Property(property="description", type="string", example="آزادسازی پول بلوکه شده به کیف پول فریلنسر"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="wallet_balance", type="integer", example=300000),
 *     @OA\Property(property="wallet_locked_balance", type="integer", example=300000),
 *     @OA\Property(property="wallet_currency", type="string", example="ریال"),
 * )
 */
class WalletTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['wallet_id', 'amount', 'transaction_type', 'description', 'related_type', 'related_id'];
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class,Wallet::class,'id','id','wallet_id','user_id');
    }

    public function getTransactionTypeValueAttribute()
    {
        switch ($this->transaction_type) {
            case 1:
                $result = 'شارژ کیف پول';
                break;
            case 2:
                $result = 'انتقال وجه از کیف پول به حساب فریلنسر';
                break;
            case 3:
                $result = 'رزرو مبلغ سفارشی که هنوز در دست اجراست';
                break;
            case 4:
                $result = 'آزادسازی مبلغ سفارشی که رزرو شده بود و انتقال آن به کیف پول فریلنسر';
                break;
            case 5:
                $result = 'بازگشت وجه رزرو شده به حساب کارفرما';
                break;
            case 6:
                $result = 'برداشت خودکار کارمزد سایت';
                break;
        }
        return $result;
    }
    public function getRelatedTypeValueAttribute()
    {
        switch ($this->related_type) {
            case 'App\\Models\\Payment\\Payment':
                $result = 'پرداخت جدید';
                break;
            case 'App\\Models\\Market\\OrderItem':
                $result = 'بخاطر آیتم سفارش';
                break;
            case 'App\\Models\\Market\\Subscription':
                $result = 'پلن اشتراک جدید';
                break;
            default:
                $result = 'نامشخص';
        }
        return $result;
    }

    public function scopeFilterByType($query, $type)
    {
        // convert 
        $transactionTypes = [
            'increase' => 1,
            'decrease' => 2,
            'hold' => 3,
            'released' => 4,
            'refund' => 5,
            'commission' => 6
        ];
        // if the type is valid filters query
        if ($type && isset($transactionTypes[$type])) {
            return $query->where('transaction_type', $transactionTypes[$type]);
        }
        // return all the payments
        return $query;
    }

}
