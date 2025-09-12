<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Portfolio",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="نمونه کار لاراول"),
 *     @OA\Property(property="description", type="string", example="این نمونه ای از پروژه لاراولی است که با لاراول 11 نوشته شده است"),
 *     @OA\Property(property="banner", type="string", format="uri", example="path/benner.extension"),
 *     @OA\Property(property="status", type="string", description="1 => active in profile, 2 => none active in profile", example="فعال در پروفایل"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *   
 *    @OA\Property(
 *          property="files",
 *          type="array",
 *                @OA\Items(  
 *                   @OA\Property(property="id", type="integer", example=3),
 *                   @OA\Property(property="file_name", type="string", example="fileName"),
 *                   @OA\Property(property="file_path", type="string", example="2356489"),
 *                   @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *                )
 *     ),
 *     @OA\Property(
 *          property="skills",
 *          type="array",
 *                @OA\Items(  
 *                   @OA\Property(property="id", type="integer", example=3),
 *                   @OA\Property(property="persian_title", type="string", example="اسم فایل"),
 *                )
 *     )
 * )
 */
class Portfolio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id','title', 'description', 'banner', 'status'];

    protected function casts()
    {
        return ['banner' => 'array'];
    }
    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function files()
    {
        return $this->morphMany('App\Models\Market\File', 'filable');
    }
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال در پروفایل';
        } else {
            return 'غیرفعال در پروفایل';
        }
    }
}
