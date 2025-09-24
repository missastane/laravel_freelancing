<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Rating",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="value", type="integer", example=4),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="rate_by", type="object",
 *         @OA\Property(property="username", type="string", example="ایمان"),
 *         @OA\Property(property="avatar", type="string", example="path/avatar.extension"),
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class Rating extends Model
{
    use HasFactory;
    protected $fillable = ['rate_by', 'ratable_type', 'ratable_id', 'value','order_id'];

    public function ratable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'rate_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
