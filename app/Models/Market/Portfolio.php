<?php

namespace App\Models\Market;

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
 *     @OA\Property(property="banner",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="status", type="string", description="1 => active in profile, 2 => none active in profile", example="فعال در پروفایل"),
 *     @OA\Property(
 *          property="freelancer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="username", type="string", example="ایمان"),
 *               )
 *            ),
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
 *                   @OA\Property(property="original_title", type="string", example="englishName"),
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
        return $this->belongsToMany(Portfolio::class);
    }
    public function files()
    {
        return $this->morphMany('App\Models\File', 'fillable');
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
