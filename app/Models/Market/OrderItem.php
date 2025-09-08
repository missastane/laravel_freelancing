<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     title="OrderItem",
 *     description="Schema for a OrderItem",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="price", type="integer", example=500000),
 *     @OA\Property(property="freelancer_amount", type="integer", example=450000),
 *     @OA\Property(property="platform_fee", type="integer", example=50000),
 *     @OA\Property(property="locked_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="due_date", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-25T12:45:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="status_value", type="string", description="status: 1 => pending, 2 => in progress, 3 => completed 4 => approved, 5 => locked, 6 => canceled", example="تأیید شده"),
 *     @OA\Property(property="locked_by_value", type="string", description="status: 1 => employer, 2 => freelancer, 3 => admin", example="فریلنسر"),
 *     @OA\Property(property="locked_reason_value", type="string", description="status: 1 => not answer, 2 => insult, 3 => poor quality, 4 => other", example="توهین"),
 *     @OA\Property(
 *          property="order",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *               )
 *            ),
 *    @OA\Property(
 *          property="milestone",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="title", type="string", example="مرحله اول"),
 *                  @OA\Property(property="description", type="string", example="ویرایش فصل های 1 تا 5")
 *               )
 *            ),
 *  @OA\Property(
 *          property="finalFiles",
 *          type="array",
 *                @OA\Items(  
 *                   @OA\Property(property="id", type="integer", example=3),
 *                   @OA\Property(property="file_name", type="string", example="fileName"),
 *                   @OA\Property(property="file_path", type="string", example="2356489"),
 *                   @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *                )
 *     )
 * )
 */
class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'proposal_milestone_id', 'status', 'locked_by', 'locked_reason', 'locked_note', 'locked_at', 'price', 'freelancer_amount', 'platform_fee', 'due_date', 'delivered_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function finalFiles()
    {
        return $this->hasMany(FinalFile::class);
    }
    public function mileestone()
    {
        return $this->belongsTo(ProposalMilestone::class);
    }
    protected function casts()
    {
        return [
            'due_date' => 'datetime',
            'delivered_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال بررسی';
                break;
            case 2:
                $result = 'در حال اجرا';
                break;
            case 3:
                $result = 'کامل شده';
                break;
            case 4:
                $result = 'تأیید شده';
                break;
            case 5:
                $result = 'قفل شده';
                break;
            case 6:
                $result = 'لغو شده';
                break;
        }
        return $result;
    }

    public function getLockedByValueAttribute()
    {
        switch ($this->locked_by) {
            case 1:
                $result = 'کارفرما';
                break;
            case 2:
                $result = 'فریلنسر';
                break;
            case 3:
                $result = 'ادمین';
                break;
        }
        return $result;
    }
    public function getLockedReasonValueAttribute()
    {
        switch ($this->locked_reason) {
            case 1:
                $result = 'عدم پاسخگویی';
                break;
            case 2:
                $result = 'توهین و فحاشی';
                break;
            case 3:
                $result = 'کیفیت پایین';
                break;
            case 4:
                $result = 'دلایل دیگر';
                break;
        }
        return $result;
    }
}
