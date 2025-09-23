<?php

namespace App\Models\Market;

use App\Models\Content\Post;
use App\Models\User\User;
use Dom\Element;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     title="Comment",
 *     description="Schema for a comment",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user", type="string", example="ایمان"),
 *     @OA\Property(property="comment", type="string", example="این محصول عالیه!"),
 *     @OA\Property(property="seen", type="string", description="Comment Seen_Value: 'دیده شده' if 1, 'دیده نشده' if 2", example="دیده شده"),
 *     @OA\Property(property="approved", type="string", description="Comment Approved_Value: 'تأیید شده' if 1, 'تأیید نشده' if 2", example="تأیید شده"),
 *     @OA\Property(property="status", type="string", description="Comment status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="commentable_type", type="string", example="نظر متعلق به یک محصول است"),
 *     @OA\Property(property="commentable_id", type="integer", example=12),
 *     @OA\Property(property="parent", type="object",
 *         @OA\Property(property="user", type="string", example="ایمان"),
 *         @OA\Property(property="comment", type="string", example="این محصول عالیه!"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-25T12:45:00Z"),
 *      ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-25T12:45:00Z"),
 * )
 */
class Comment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'parent_id',
        'comment',
        'commentable_type',
        'commentable_id',
        // this fields have default values:
        // 'seen',
        // 'approved',
        // 'status' 
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function getCommentableTypeValueAttribute()
    {
        switch ($this->commentable_type) {
            case Post::class:
                $result = 'نظر متعلق به یک پست است';
                break;
            case Order::class:
                $result = 'نظر متعلق به یک سفارش است';
                break;
            default:
            $result = 'نامشخص';
        }
        return $result;
    }
    public function getSeenValueAttribute()
    {
        if ($this->seen == 1) {
            return 'دیده شده';
        } else {
            return 'دیده نشده';
        }
    }
    public function getApprovedValueAttribute()
    {
        if ($this->approved == 1) {
            return 'تأیید شده';
        } else {
            return 'تأیید نشده';
        }
    }
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }

}
