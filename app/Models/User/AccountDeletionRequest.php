<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDeletionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 1;
    public const STATUS_CANCELLED = 2;
    public const STATUS_COMPLETED = 3;
    public const STATUS_FAILED = 4;

    protected $fillable = [
        'user_id',
        'status',
        'reason',
        'scheduled_for',
        'processed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
