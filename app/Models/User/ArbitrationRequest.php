<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ArbitrationRequest",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="dispute-request", type="object",
 *        @OA\Property(property="employer", type="object",
 *           @OA\Property(property="username", type="string", example="razi"),
 *        ),
 *        @OA\Property(property="freelancer", type="object",
 *           @OA\Property(property="username", type="string", example="roody"),
 *        ),
 *        @OA\Property(
 *          property="plaintiff",
 *          type="object",
 *                  @OA\Property(property="username", type="string", example="roody"),
 *                  @OA\Property(property="role", type="string", example="employer"),
 *         ),
 *        @OA\Property(property="order_item", type="object",
 *           @OA\Property(property="title", type="string", example="مرحله اول پیشنهاد"),
 *           @OA\Property(property="due_date", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *           @OA\Property(property="price", type="integer", example=500000),
 *           @OA\Property(property="delivered_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *         ),
 *        @OA\Property(property="reason", type="string", example="عدم تحویل به موقع کار توسط فریلنسر"),
 *        @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     ),
 *     @OA\Property(property="status", type="string",description="	1 => pending, 2 => For the benefit of the employer(cancel), 3 => For the benefit of the freelancer(approve delivery), 4 => Money distribution, 5 => without change", example="به نفع کارفرما"),
 *     @OA\Property(property="freelancer_percent", type="integer", example=50),
 *     @OA\Property(property="employer_percent", type="integer", example=50),
 *     @OA\Property(property="result_description", type="string", example="من به نفع هیچ کس رای ندادم و پول رو تقسیم کردم"),
 *     @OA\Property(property="resolved_by", type="string", example="ایمان مدائنی"),
 *     @OA\Property(property="resolved_at", type="string", format="date-time", description="resolved datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class ArbitrationRequest extends Model
{
    use HasFactory;
    protected $fillable = ['dispute_request_id', 'status', 'freelancer_percent', 'employer_percent', 'description', 'resolved_by', 'resolved_at'];

    public function disputeRequest()
    {
        return $this->belongsTo(DisputeRequest::class, 'dispute_request_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class,'resolved_by');
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
            'employer' => 2,
            'freelancer' => 3,
            'distribution' => 4,
            'nochange' => 5,
        ];

        // if the type is valid filters query
        if (isset($requestStatuses[$status])) {
            return $query->where('status', $requestStatuses[$status]);
        }
        // return all the payments
        return $query;
    }

}
