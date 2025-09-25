<?php

namespace App\Models\Content;

use App\Models\Market\File;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="summary", type="string", example="خلاصه ای از تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="content", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\post\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="study_time", type="string", example="2 دقیقه"),
 *     @OA\Property(property="view", type="integer", example=0),
 *     @OA\Property(property="published_at", description="publish datetime", type="string", format="date-time", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="related_posts_value", type="array",
 *       @OA\Items(type="object",
 *           @OA\Property(property="id", type="integer", example=3),
 *           @OA\Property(property="title", type="string", example="تازه های دیجیتال"),
 *           @OA\Property(property="slug", type="string", example="slug")
 *         )
 *            ),
 *        @OA\Property(
 *          property="postCategory",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تازه های دیجیتال")
 *               )
 *            ),
 *    @OA\Property(
 *          property="author",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تازه های دیجیتال")
 *               )
 *        ),
 * )
 */
class Post extends Model
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
    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'image',
        'study_time',
        'related_posts',
        'view',
        'published_at'
    ];
    protected function casts()
    {
        return [
            'related_posts' => 'array',
            'published_at' => 'datetime',
            'image' => 'array'
        ];
    }
    protected $hidden = ['category_id', 'author_id', 'related_posts'];
    public function postCategory()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function comments()
    {
        return $this->morphMany('App\Models\Content\Comment', 'commentable');
    }
    public function rates()
    {
        return $this->morphMany('App\Models\Rating', 'ratable');
    }
    public function favorites()
    {
        return $this->morphMany('App\Models\User\Favorite', 'favoritable');
    }
    public function files()
    {
        return $this->morphMany(File::class, 'filable');
    }
    public function getRelatedPostsValueAttribute()
    {
        if ($this->related_posts !== "null") {
            $posts = Post::whereIn('id', $this->related_posts)->select('id', 'title', 'slug')->get();
            return $posts;
        }
        return null;
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
