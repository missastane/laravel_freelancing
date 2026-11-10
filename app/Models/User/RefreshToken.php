<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    protected $fillable = [
        'session_id',
        'token_hash',
        'expires_at',
        'used_at',
        'revoked_at',
        'replaced_by_token_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function authSession(): BelongsTo
    {
        return $this->belongsTo(AuthSession::class, 'session_id');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'replaced_by_token_id'
        );
    }
}
