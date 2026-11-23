<?php

namespace App\Jobs;

use App\Notifications\EmailChangeVerificationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendEmailChangeVerification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $newEmail,
        public string $plainToken
    ) {}

    public function handle(): void
    {
        Notification::route('mail', $this->newEmail)
            ->notify(
                new EmailChangeVerificationNotification(
                    $this->newEmail,
                    $this->plainToken
                )
            );
    }
}