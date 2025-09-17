<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *          @OA\Property(property="message", type="string", example="کاربر جدید در سیستم ثبت نام شد"),
 *          @OA\Property(property="read_at", type="string", format="date-time", description="read datetime", example="2025-02-22T10:00:00Z"),
 *          @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    protected function casts()
    {
        return [
            'data' => 'array'
        ];
    }

}
