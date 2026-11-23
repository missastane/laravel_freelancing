<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EmailChangeVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $newEmail,
        public string $plainToken
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'email-change.verify',
            now()->addMinutes(30),
            [
                'token' => $this->plainToken,
            ]
        );

        return (new MailMessage)
            ->subject('Verify your new email address')
            ->greeting('Hello!')
            ->line('You requested to change your account email address.')
            ->line('New email address: ' . $this->newEmail)
            ->action('Verify New Email', $verificationUrl)
            ->line('This link expires in 30 minutes.')
            ->line('If you did not request this change, you can safely ignore this email.');
    }
}
