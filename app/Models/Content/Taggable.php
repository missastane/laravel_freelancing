<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taggable extends Model
{
    use HasFactory;
    protected $fillable = ['tag_id', 'taggable_type', 'taggable_id'];
    protected $hidden = ['tag_id', 'taggable_type'];
    protected $appends = ['taggable_type_value'];
    protected $table = 'taggables';
    public function taggable()
    {
        return $this->morphTo();
    }
    public function getTaggableTypeValueAttribute()
    {
        switch ($this->taggable_type) {
            case 'App\Models\Market\Project':
                $result = 'پروژه';
                break;
            case 'App\Models\Market\ProjectCategory':
                $result = 'دسته بندی پروژه';
                break;
            case 'App\Models\Content\Post':
                $result = 'پست';
                break;
            case 'App\Models\Content\PostCategory':
                $result = 'دسته بندی پست';
                break;
            case 'App\Models\Content\Faq':
                $result = 'سؤالات متداول';
                break;
            case 'App\Models\Setting\Setting':
                $result = 'تنظیمات';
                break;
            default:
                $result = 'نامشخص';
        }
        return $result;
    }
}
