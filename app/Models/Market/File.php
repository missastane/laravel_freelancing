<?php

namespace App\Models\Market;

use App\Models\Content\Post;
use App\Models\Ticket\TicketMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'filable_type',
        'filable_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_type',
        'file_size',
        'is_final_delivery',
        'uploaded_by'
    ];

    public function filable()
    {
        return $this->morphTo();
    }

    public function getFilableTypeValueAttribute()
    {
        switch($this->filable_type)
        {
            case 'App\Models\Project' :
                $result = 'فایل متعلق به یک پروژه است';
                break;
            case 'App\Models\Portfolio':
                 $result = 'فایل متعلق به یک نمونه کار است';
                break;
             case 'App\Models\Message':
                 $result = 'فایل متعلق به یک پیام است';
                break;
             case 'App\Models\TicketMessage':
                 $result = 'فایل متعلق به پیام یک تیکت است';
                break;
              case 'App\Models\Post':
                 $result = 'فایل متعلق به یک پست است';
                break;
        }
        return $result;
    }
}
