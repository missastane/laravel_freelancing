<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Schema for a Order",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="project", type="object",
 *        @OA\Property(property="employer", type="string", example="username"),
 *        @OA\Property(property="title", type="string", example="برنامه نویسی لاراول"),
 *        @OA\Property(property="slug", type="string", example="برنامه-نویسی-لاراول"),
 *        @OA\Property(property="description", type="string", example= "در این پروژه ما می خواهیم که یک پلتفرم را با فریم ورک لاراول پیاده سازی و اجرا کنیم."),
 *        @OA\Property(property="duration_time", type="integer", example=15),
 *        @OA\Property(property="amount", type="decimal", example=7000000.000),
 *     ),
 *     @OA\Property(property="proposal", type="object",
 *        @OA\Property(property="freelancer", type="string", example="username"),
 *        @OA\Property(property="description", type="string", example= "من می توانم پلتفرم درخواستی شما را با فریم ورک لاراول پیاده سازی و اجرا کنم."),
 *        @OA\Property(property="total_amount", type="decimal", example=70000000.000),
 *        @OA\Property(property="due_date", type="string", format="datetime",description="due_date datetime", example="2025-02-22T14:30:00Z"),
 *     ),
 *     @OA\Property(property="orderItems", type="array",
 *        @OA\Items(
 *           @OA\Property(property="id", type="integer", example=1),
 *           @OA\Property(property="title", type="string", example="مرحله اول پیشنهاد"),
 *           @OA\Property(property="description", type="string", example= "من می توانم پلتفرم درخواستی شما را با فریم ورک لاراول پیاده سازی و اجرا کنم."),
 *           @OA\Property(property="price", type="integer", example=500000),
 *           @OA\Property(property="freelancer_amount", type="integer", example=450000),
 *           @OA\Property(property="platform_fee", type="integer", example=50000),
 *           @OA\Property(property="due_date", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *           @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *        )
 *     ),
 *     @OA\Property(property="total_price", type="integer", example=500000),
 *     @OA\Property(property="due_date", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="status", type="string", description="status: 1 => pending, 2 => in progress, 3 => completed, 4 => canceled	", example="در حال بررسی"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-25T12:45:00Z"),
 *     @OA\Property(property="comments", type="array",
 *        @OA\Items(
 *           @OA\Property(property="id", type="integer", example=1),
 *           @OA\Property(property="user", type="string", example="username"),
 *           @OA\Property(property="comment", type="string", example= "من می توانم پلتفرم درخواستی شما را با فریم ورک لاراول پیاده سازی و اجرا کنم."),
 *        )
 *     )
 *  )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'project_id', 'employer_id', 'freelancer_id', 'status', 'total_price', 'delivered_at', 'due_date'];

    protected function casts()
    {
        return [
            'due_date' => 'datetime',
            'delivered_at' => 'datetime'
        ];
    }
    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }


    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function finalFiles()
    {
        return $this->hasManyThrough(FinalFile::class, OrderItem::class, 'order_id', 'order_item_id', 'id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'order_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
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
                $result = 'لغو شده';
                break;
        }
        return $result;
    }

    public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $orderStatuses = [
            'pending' => 1,
            'processing' => 2,
            'completed' => 3,
            'canceled' => 4,
        ];

        // if the type is valid filters query
        if (isset($orderStatuses[$status])) {
            return $query->where('status', $orderStatuses[$status]);
        }
        // return all the payments
        return $query;
    }
}
