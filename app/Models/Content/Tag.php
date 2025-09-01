<?php

namespace App\Models\Content;

use App\Models\Market\ProjectCategory;
use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="برنامه نویسی"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *        @OA\Property(
 *          property="taggable",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="taggable_id", description="Id of Object which related to Current tag", type="integer", example=3),
 *                  @OA\Property(property="taggable_type_value", type="string", example="کالای دیجیتال")
 *               )
 *          ), 
 *    )
 */
class Tag extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name'];

    public function taggables()
    {
        return $this->hasMany(Taggable::class, 'tag_id');
    }

    public function postCategories()
    {
        return $this->morphedByMany(PostCategory::class, 'taggable');
    }
    public function projectCategories()
    {
        return $this->morphedByMany(ProjectCategory::class, 'taggable');
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }


    public function settings()
    {
        return $this->morphedByMany(Setting::class, 'taggable');
    }

}
