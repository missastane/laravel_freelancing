<?php

namespace App\Models\Ticket;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="TicketMessage",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="author", type="string", example="useraname"),
 *     @OA\Property(property="author_role", type="string", example="کارفرما"),
 *     @OA\Property(property="message", type="string", example="هنوزم پول به حسابم واریز نشده"),
 *     @OA\Property(property="parent", type="object", 
 *       @OA\Property(property="id", type="integer", example=1),
 *       @OA\Property(property="message", type="string", example="هنوزم پول به حسابم واریز نشده"),
 *       @OA\Property(property="files", type="array", 
 *          @OA\Items(type="object", 
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="file_name", type="string", example="fileName"),
 *             @OA\Property(property="file_path", type="string", example="path/file.extension"),
 *             @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *          ),
 *       ),
 *     ),
 *     @OA\Property(property="files", type="array", 
 *          @OA\Items(type="object", 
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="file_name", type="string", example="fileName"),
 *             @OA\Property(property="file_path", type="string", example="path/file.extension"),
 *             @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *          ),
 *       ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class TicketMessage extends Model
{
    use HasFactory, SoftDeletes;
    protected static function newFactory()
    {
        return \Database\Factories\TicketMessageFactory::new();
    }
    protected $fillable = ['ticket_id', 'author_id', 'message', 'author_type', 'parent_id'];
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function parent()
    {
        return $this->belongsTo(TicketMessage::class, 'parent_id');
    }
    public function files()
    {
        return $this->morphMany('App\Models\Market\File', 'filable');
    }
    public function getAuthorTypeValueAttribute()
    {
        switch ($this->author_type) {
            case 1:
                $result = 'کارفرما';
                break;
            case 2:
                $result = 'فریلنسر';
                break;
            case 3:
                $result = 'ادمین';
                break;
        }
        return $result;
    }

}
