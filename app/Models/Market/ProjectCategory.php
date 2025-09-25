<?php

namespace App\Models\Market;

use App\Models\Content\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
/**
 * @OA\Schema(
 *     schema="ProjectCategory",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="ترجمه"),
 *     @OA\Property(property="description", type="string", example="پروژه های ترجمه"),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="image", type="string", format="uri", example="\path\image.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="User status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="show_in_menu_value", type="string", description="Show In Menu Value: 'yes' if 1, 'no' if 2", example="بله"),
 *     @OA\Property(
 *          property="parent",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="برنامه نویسی")
 *               )
 *            ),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تکنولوژی")
 *               )
 *            ),
 * )
 */
class ProjectCategory extends Model
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
                'source' => 'name'
            ]
        ];
    }
    protected $fillable = ['name', 'slug', 'description', 'image', 'parent_id', 'status', 'show_in_menu'];

    protected function casts()
    {
        return [
            'image' => 'array'
        ];
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectCategory::class, 'parent_id');
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }

    public function getShowInMenuValueAttribute()
    {
        if ($this->show_in_menu == 1) {
            return 'بله';
        } else {
            return 'خیر';
        }
    }
}
