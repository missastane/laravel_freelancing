<?php

namespace App\Models\Market;

use App\Models\Content\Tag;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="برنامه نویسی لاراول"),
 *     @OA\Property(property="slug", type="string", example="برنامه-نویسی-لاراول"),
 *     @OA\Property(property="description", type="string", example= "در این پروژه ما می خواهیم که یک پلتفرم را با فریم ورک لاراول پیاده سازی و اجرا کنیم."),
 *     @OA\Property(property="duration_time", type="integer", example=15),
 *     @OA\Property(property="amount", type="decimal", example=7000000.000),
 *     @OA\Property(property="status", type="string", description="1 => pending, 2 => in progress , 3 => completed, 4 => canceled", example="تکمیل شده"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(
 *          property="employer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 *    @OA\Property(
 *          property="category",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="برنامه نویسی")
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
 *    @OA\Property(
 *          property="skills",
 *          type="array",
 *                @OA\Items(  
 *                   @OA\Property(property="id", type="integer", example=3),
 *                   @OA\Property(property="persian_title", type="string", example="ورد"),
 *                   @OA\Property(property="original_title", type="string", example="word"),
 *                )
 *     )
 *     )
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;
       use Sluggable;
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
    protected $fillable = ['user_id', 'project_category_id', 'title', 'slug', 'description', 'duration_time', 'amount', 'status'];
    protected $hidden = ['project_category_id', 'status', 'user_id', 'is_full_time'];
    public function employer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'project_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'project_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function files()
    {
        return $this->morphMany('App\Models\Market\File', 'filable');
    }

    public function favorites()
    {
        return $this->morphMany('App\Models\User\Favorite', 'favoritable');
    }
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال بررسی توسط فریلنسرها';
                break;
            case 2:
                $result = 'در جریان اجرا توسط یک فریلنسر';
                break;
            case 3:
                $result = 'تکمیل شده';
                break;
            case 4:
                $result = 'لغو شده';
                break;
        }
        return $result;
    }
    private function convertToCodes($statuses, $mapping)
    {
        // get statuses('processing, sending,canceling) and return an array and convert to a cellection
        return collect(explode(',', $statuses))
            ->map(fn($s) => $mapping[trim($s)] ?? (is_numeric($s) ? (int) $s : null))
            ->filter()
            ->toArray();
        // after enters a loop and each member will be trim. if a member exist in mapping , returns a code
        // will be returns as int unless returns null. then null will be removed by filter method and convert toarray.
    }
    private function getStatusCodes($statuses)
    {
        return $this->convertTocodes(
            $statuses,
            [
                'pending' => 1,
                'processing' => 2,
                'completed' => 3,
                'canceled' => 4
            ]
        );
    }
    public function scopeFilter($query, $filters)
    {
        return $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->whereIn('status', $this->getStatusCodes($filters['status']));
        })->when(isset($filters['category_id']), function ($q) use ($filters) {
            $q->where('project_category_id', $filters['category_id']);
        });
    }
}
