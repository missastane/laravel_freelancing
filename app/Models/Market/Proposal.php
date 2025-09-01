<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Proposal",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="description", type="string", example= "من می توانم پلتفرم درخواستی شما را با فریم ورک لاراول پیاده سازی و اجرا کنم."),
 *     @OA\Property(property="total_amount", type="decimal", example=70000000.000),
 *     @OA\Property(property="total_duration_time", type="integer", example=15),
 *     @OA\Property(property="due_date", type="string", format="datetime",description="due_date datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="status_value", type="string", description="1 => pending, 2 => approved, 3 => rejected", example="پذیرفته شده"),
 *     @OA\Property(
 *          property="freelancer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 *    @OA\Property(
 *          property="project",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="title", type="string", example="برنامه نویسی")
 *               )
 *            )
 *     )
 */
class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_id', 'freelancer_id', 'description', 'total_amount', 'total_duration_time', 'status', 'due_date'];

    protected $hidden = ['project_id', 'freelancer_id', 'status'];
    protected function casts()
    {
        return ['due_date' => 'datetime'];
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function milstones()
    {
        return $this->hasMany(ProposalMilstone::class);
    }
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'در حال بررسی';
                break;
            case 2:
                $result = 'پذیرفته شده';
                break;
            case 3:
                $result = 'رد شده';
                break;
            case 4:
                $result = 'پس گرفته شده';
                break;
        }
        return $result;
    }

    public function setDueDateAttribute()
    {
        $dueDate = now('Asia/Tehran')->addDays($this->total_duration_time)->getTimestamp();
        return $dueDate;
    }

    public function setTotalAmountAttribute()
    {
        $milstones = $this->milstones();
        $amounts = null;
        foreach ($milstones as $milstone) {
            $amounts += $milstone->amount;
        }
        return $amounts;
    }

    public function setTotalDurationTimeAttribute()
    {
        $milstones = $this->milstones();
        $duration = null;
        foreach ($milstones as $milstone) {
            $duration += $milstone->duration_time;
        }
        return $duration;
    }

    public function scopeFilterByStatus($query, $status)
    {
        // convert 
        $proposalStatuses = [
            'pending' => 1,
            'approved' => 2,
            'rejected' => 3,
            'withdrawn' => 4
        ];

        // if the type is valid filters query
        if (isset($proposalStatuses[$status])) {
            return $query->where('status', $proposalStatuses[$status]);
        }
        // return all the proposals
        return $query;
    }
}
