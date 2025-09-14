<?php

namespace App\Notifications;

use App\Broadcasting\SendWithoutLimit\SmsChannel;
use App\Models\Market\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendFinalFileNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Project $project, protected string $message)
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
        return ['mail', 'database', SmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ارسال فایل پروژه {$this->project->title}")
            // ->action('Notification Action', url('/'))
            ->line("فایل پروژه {$this->project->title} توسط فریلنسر برای شما ارسال شد. لطفا آن را بررسی کنید");
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
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function toSms($notifiable): ?string
    {
        return $this->message;
    }
}
