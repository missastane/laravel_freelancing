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
        'is_final_delivery'
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
                $result = Project::where('id', $this->filable_id)->select('title')->get();
                break;
            case 'App\Models\Portfolio':
                 $result = Portfolio::where('id', $this->filable_id)->select('title')->get();
                break;
             case 'App\Models\Message':
                 $result = Message::where('id', $this->filable_id)->select('message', 'message_context', 'message_context_id')->get();
                break;
             case 'App\Models\TicketMessage':
                 $result = TicketMessage::where('id', $this->filable_id)->select('message')->get();
                break;
              case 'App\Models\Post':
                 $result = Post::where('id', $this->filable_id)->select('title')->get();
                break;
        }
        return $result;
    }
}
