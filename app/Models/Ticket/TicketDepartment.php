<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="TicketDepartment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="مالی"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="delete datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="status_value", type="string",description="status: 1 => active, 2 => disactive	", example="فعال"),
 * )
 */
 class TicketDepartment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'status'];
    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'فعال';
                break;
            case 2:
                $result = 'غیرفعال';
                break;
        }
        return $result;
    }
      public function priorities()
    {
        return $this->hasMany(Ticket::class);
    }
}
