<?php

namespace App\Jobs;

use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendVerificationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->sendEmailVerificationNotification();
    }
}
