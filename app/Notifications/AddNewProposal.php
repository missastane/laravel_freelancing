<?php

namespace App\Notifications;

use App\Broadcasting\SendWithLimit\EmailChannel;
use App\Broadcasting\SendWithLimit\NotificationChannel;
use App\Broadcasting\SendWithLimit\SmsChannel;
use App\Models\Market\Project;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddNewProposal extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct( protected string $message, protected Project $project)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SmsChannel::class, EmailChannel::class, NotificationChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ثبت پیشنهاد جدید برای پروژه شما با عنوان {$this->project->title}")
            ->line($this->message);
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
            'message' => $this->message,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public function toSms($notifiable): ?string
    {
        return $this->message;
    }
}
