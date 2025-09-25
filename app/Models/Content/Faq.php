<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

/**
 * @OA\Schema(
 *     schema="Faq",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="question", type="string", example="چگونه پیشنهادی ارائه دهم"),
 *     @OA\Property(property="answer", type="string", example="با کلیک کردن روی یک پروژه میتوانید پیشنهاد خود را ثبت نمایید"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *    
 * )
 */
class Faq extends Model
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
                'source' => 'question'
            ]
        ];
    }
    protected $table = 'faq';
    protected $fillable = ['question', 'answer', 'slug', 'status'];
    protected $hidden = ['status'];
    protected $appends = ['status_value'];
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
