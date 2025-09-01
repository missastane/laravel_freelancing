<?php

namespace App\Models\Market;

use App\Models\Market\Message;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageUserStatus extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'receiver_id', 'status', 'delivered_at', 'read_at'];

    protected function casts()
    {
        return [
            'read_at' => 'datetime',
            'delivered_at' => 'datetime'
        ];
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }


     public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getStatusValueAttribute()
    {
        switch($this->status)
        {
            case 1:
                $result = 'ارسال شده';
                break;
            case 2:
                $result = 'تحویل داده شده';
                break;
            case 3:
                $result = 'دیده شده';
                break;
        }
        return $result;
    }
}
