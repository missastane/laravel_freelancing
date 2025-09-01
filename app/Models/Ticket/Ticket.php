<?php

namespace App\Models\Ticket;

use App\Models\Market\Order;
use App\Models\User\DisputeRequest;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Ticket",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="subject", type="string", example="عدم بازگشت پول به حساب کارفرما"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="delete datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="ticket_type_value", type="string",description="type: 1 => support, 2 => report , 3 => financial, 4 => complain", example="مالی"),
 *     @OA\Property(property="status_value", type="string",description="status: 1 => open, 2 => answered, 3 => closed", example=""),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 *    @OA\Property(
 *          property="priority",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="بسیار زیاد"),
 *               )
 *            ),
 *   @OA\Property(
 *          property="department",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="پشتیبانی"),
 *               )
 *            ),
 *   @OA\Property(
 *          property="disputeRequest",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="reason", type="string", example="عدم تحویل به موقع فریلنسر"),
 *               )
 *            ),
 * 
 * )
 */
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'priority_id', 'department_id', 'dispute_request_id', 'ticket_type', 'subject', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }
    public function department()
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }
    public function disputeRequest()
    {
        return $this->belongsTo(DisputeRequest::class, 'dispute_request_id');
    }
    public function getTicketTypeValueAttribute()
    {
        switch ($this->ticket_type) {
            case 1:
                $result = 'پشتیبانی';
                break;
            case 2:
                $result = 'گزارش';
                break;
            case 3:
                $result = 'مالی';
                break;
            case 4:
                $result = 'شکایت';
                break;
        }
        return $result;
    }
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'باز';
                break;
            case 2:
                $result = 'پاسخ داده شده';
                break;
            case 3:
                $result = 'بسته';
                break;
        }
        return $result;
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class);
    }

      public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $ticketStatuses = [
            'open' => 1,
            'answered' => 2,
            'closed' => 3
        ];

        // if the type is valid filters query
        if (isset($ticketStatuses[$status])) {
            return $query->where('status', $ticketStatuses[$status]);
        }
        // return all the payments
        return $query;
    }
}
