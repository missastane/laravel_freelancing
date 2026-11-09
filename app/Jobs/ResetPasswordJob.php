<?php

namespace App\Jobs;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResetPasswordJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user, protected $token)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new ResetPasswordNotification($this->token, ['message' => __('messages.password_recovery_link_sent_to_email', ['email' => $this->user->email])]));

    }
}
