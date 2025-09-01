<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArbitrationRequest extends Model
{
    use HasFactory;
    protected $fillable = ['dispute_request_id', 'status', 'freelancer_percent', 'employer_percent', 'description', 'resolved_by', 'resolved_at'];

    public function disputeRequest()
    {
        return $this->belongsTo(DisputeRequest::class, 'dispute_request_id');
    }
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال بررسی';
                break;
            case 2:
                $result = 'تمام شده به نفع کارفرما(فسخ قرارداد)';
                break;
            case 3:
                $result = 'تمام شده به نفع فریلنسر (کنسل شدن بقیه مراحل پروژه)';
                break;
            case 4:
                $result = 'تقسیم پول بین کارفرما و فریلنسر';
                break;
            case 5:
                $result = 'بدون تغییر و ادامه پروژه';
                break;
        }
        return $result;
    }
    public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $requestStatuses = [
            'pending' => 1,
            'resolved' => 2,
            'rejected' => 3
        ];

        // if the type is valid filters query
        if (isset($requestStatuses[$status])) {
            return $query->where('status', $requestStatuses[$status]);
        }
        // return all the payments
        return $query;
    }

}
