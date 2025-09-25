<?php

namespace App\Models\User;

use App\Models\Market\FinalFile;
use App\Models\Market\OrderItem;
use App\Models\Ticket\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="DisputeRequest",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *          property="employer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="username", type="string", example="razi"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="freelancer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="username", type="string", example="roody"),
 *               )
 *            ),
 *     @OA\Property(property="orderItem", type="object",
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="title", type="string", example="مرحله اول پیشنهاد"),
 *          @OA\Property(property="due_date", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *          @OA\Property(property="price", type="integer", example=500000),
 *          @OA\Property(property="freelancer_amount", type="integer", example=450000),
 *          @OA\Property(property="platform_fee", type="integer", example=50000),
 *          @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *          @OA\Property(property="order_id", type="integer", example=3)
 *      ),
 *      @OA\Property(property="finalFile", type="object",
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="file_name", type="string", example="مرحله اول پیشنهاد"),
 *          @OA\Property(property="file_path", type="string", example="path/file.extension"),
 *          @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *      ),
 *     @OA\Property(
 *          property="plaintiff",
 *          type="object",
 *                  @OA\Property(property="username", type="string", example="roody"),
 *                  @OA\Property(property="role", type="string", example="employer"),
 *               )
 *            ),
 *     @OA\Property(property="reason", type="string", example="عدم تحویل به موقع کار توسط فریلنسر"),
 *     @OA\Property(property="status", type="string",description="status: 1 => pending, 2 => resloved, 3 => rejected", example="رد شده"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class DisputeRequest extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['order_item_id', 'final_file_id', 'user_type', 'raised_by', 'reason', 'status'];

    protected function casts()
    {
        return [
            'resolved_at' => 'datetime'
        ];
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }
    public function finalFile()
    {
        return $this->belongsTo(FinalFile::class, 'final_file_id');
    }

    public function disputeTicket()
    {
        return $this->hasOne(Ticket::class, 'dispute_request_id');
    }

    public function getUserTypeValueAttribute()
    {
        switch ($this->user_type) {
            case 1:
                $result = 'کارفرما';
                break;
            case 2:
                $result = 'فریلنسر';
                break;

        }
        return $result;
    }
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال بررسی';
                break;
            case 2:
                $result = 'حل شده';
                break;
            case 3:
                $result = 'پس گرفته شده';
                break;
            case 4:
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
            'resolved' => 2,
            'withdrawn' => 3,
            'rejected' => 4
        ];

        // if the type is valid filters query
        if (isset($requestStatuses[$status])) {
            return $query->where('status', $requestStatuses[$status]);
        }
        // return all the payments
        return $query;
    }

}
