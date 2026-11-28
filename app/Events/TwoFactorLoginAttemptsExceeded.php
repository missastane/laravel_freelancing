<?php

namespace App\Events;

use App\Models\User\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TwoFactorLoginAttemptsExceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public array $challenge
    ) {}
}