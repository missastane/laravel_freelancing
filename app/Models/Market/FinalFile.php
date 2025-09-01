<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalFile extends Model
{
    use HasFactory;
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
        switch($this->status)
        {
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
        switch($this->rejected_type)
        {
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
