<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="FinalFile",
 *     type="object",
 *     title="FinalFile",
 *     description="Schema for a FinalFile",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="file_name", type="string", example="fileName"),
 *     @OA\Property(property="file_path", type="string", example="2356489"),
 *     @OA\Property(property="mime_type", type="string", example="image/jpg"),
 *     @OA\Property(property="file_size", type="integer", example=401093),
 *     @OA\Property(property="download_url", type="string", example="http://127.0.0.1:8000/api/final-file/download/21"),
 *  )
 */
class FinalFile extends Model
{
    use HasFactory;
    protected static function newFactory()
    {
        return \Database\Factories\FinalFileFactory::new();
    }
    protected $fillable = [
        'order_item_id',
        'file_id',
        'status',
        'freelancer_id',
        'delivered_at',
        'employer_id',
        'revision_at',
        'revision_note',
        'approved_at',
        'rejected_at',
        'rejected_type',
        'rejected_note',
    ];
    protected function casts()
    {
        return [
            'delivered_at' => 'datetime',
            'revision_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime'
        ];
    }
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'معلق';
                break;
            case 2:
                $result = 'تأیید شده';
                break;
            case 3:
                $result = 'رد شده';
                break;
        }
        return $result;
    }

    public function getRejectedTypeValueAttribute()
    {
        switch ($this->rejected_type) {
            case 1:
                $result = 'جهت بازبینی';
                break;
            case 2:
                $result = 'رد کامل فریلنسر';
                break;
        }
        return $result;
    }
}
