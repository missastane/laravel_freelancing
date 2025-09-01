<?php

namespace App\Models\Ticket;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="TicketMessage",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="message", type="string", example="هنوزم پول به حسابم واریز نشده"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="delete datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="author_type_value", type="string", description="type: 1 => employer, 2 => freelancer, 3 => admin", example="ادمین"),
 *     @OA\Property(
 *          property="ticket",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="subject", type="string", example="عدم بازگشت پول به حساب کارفرما"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="author",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="parent",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="message", type="string", example="هنوزم پول به حسابم واریز نشده"),
 *               )
 *            ),
 * 
 * )
 */
class TicketMessage extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['ticket_id', 'author_id', 'message', 'author_type', 'parent_id'];
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function parent()
    {
        return $this->belongsTo(TicketMessage::class, 'parent_id');
    }
    public function files()
    {
        return $this->morphMany('App\Models\File', 'fillable');
    }
    public function getAuthorTypeValueAttribute()
    {
        switch ($this->user_type) {
            case 1:
                $result = 'کارفرما';
            case 2:
                $result = 'فریلنسر';
            case 3:
                $result = 'ادمین';
        }
        return $result;
    }

}
