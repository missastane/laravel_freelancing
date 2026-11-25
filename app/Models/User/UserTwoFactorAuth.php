<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorAuth extends Model
{
    protected $table = 'user_two_factor_auth';

    protected $fillable = [
        'user_id',
        'secret',
        'confirmed_at',
        'setup_expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'confirmed_at' => 'datetime',
            'setup_expires_at' => 'datetime',
            'last_used_at' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEnabled(): bool
    {
        return $this->confirmed_at !== null;
    }
}
