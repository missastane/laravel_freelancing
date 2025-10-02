<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Message",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="sender", type="string", example="ali001"),
 *     @OA\Property(property="message", type="string", example="چه نوع ویرایشی مد نظرتون هست؟"),
 *     @OA\Property(property="files", type="array",
 *        @OA\Items(  
 *            @OA\Property(property="id", type="integer", example=3),
 *            @OA\Property(property="file_name", type="string", example="fileName"),
 *            @OA\Property(property="file_path", type="string", example="2356489"),
 *            @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *            @OA\Property(property="download_url", type="string", example="http://127.0.0.1:8000/api/final-file/download/21"),
 *         )
 *     ),
 *     @OA\Property(property="sent_date", type="string", format="date-time", description="sent datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="parent", type="object",
 *        @OA\Property(property="id", type="integer", example=1),
 *        @OA\Property(property="sender", type="string", example="ali001"),
 *        @OA\Property(property="message", type="string", example="چه نوع ویرایشی مد نظرتون هست؟"),
 *        @OA\Property(property="files", type="array",
 *          @OA\Items(  
 *            @OA\Property(property="id", type="integer", example=3),
 *            @OA\Property(property="file_name", type="string", example="fileName"),
 *            @OA\Property(property="file_path", type="string", example="2356489"),
 *            @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *            @OA\Property(property="download_url", type="string", example="http://127.0.0.1:8000/api/final-file/download/21"),
 *          )
 *     ),
 *        @OA\Property(property="sent_date", type="string", format="date-time", description="sent datetime", example="2025-02-22T10:00:00Z"),
 *     ),
 * )
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;
  protected static function newFactory()
    {
        return \Database\Factories\MessageFactory::new();
    }
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'message_type',
        'sent_date',
        'parent_id',
        'message_context',
        'message_context_id'
    ];

    protected function casts()
    {
        return ['sent_date' => 'datetime'];
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'filable');
    }
    public function getMessageTypeValueAttribute()
    {
        switch ($this->message_type) {
            case 1:
                $result = 'پیام متنی';
                break;
            case 2:
                $result = 'پیام مالتی مدیا';
                break;
            case 3:
                $result = 'پیام متنی و مالتی مدیا';
                break;
        }
        return $result;
    }


}
