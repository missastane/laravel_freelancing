<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    protected $token;
    private $details;
    /**
     * Create a new notification instance.
     */
    public function __construct($token, $details)
    {
        $this->token = $token;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = rtrim(config('services.frontend_url'), '/') .
            '/auth/reset-password?' .
            http_build_query([
                'token' => $this->token,
                'email' => $notifiable->email,
            ]);

        return (new MailMessage)
            ->subject(__('entity.password_recovery'))
            ->line(__('messages.recovery_password_request_delivered'))
            ->action(__('entity.reset_password'), $resetUrl)
            ->line(__('messages.ignore_reset_password'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        date_default_timezone_set('Asia/Tehran');
        return [
            'message' => $this->details['message'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
