<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="App\Notifications\NewUserRegister"),
 *     @OA\Property(property="data", type="array", 
 *       @OA\Items(
 *          @OA\Property(property="message", type="string", example="کاربر جدید در سیستم ثبت نام شد"),
 *          @OA\Property(property="datetime", type="string", format="date-time", example="2025-02-22T10:00:00Z"),
 *        )
 *      ),
 *     @OA\Property(property="read_at", type="string", format="date-time", description="read datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
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

    public function getUserAttribute()
    {
        $user = User::where('id',$this->notifiable_id)
        ->select(['id','first_name','last_name'])->first();
        return $user;
    }
}
