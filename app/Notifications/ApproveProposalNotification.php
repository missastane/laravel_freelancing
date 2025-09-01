<?php

namespace App\Notifications;

use App\Broadcasting\SendWithoutLimit\SmsChannel;
use App\Models\Market\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApproveProposalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Proposal $proposal, protected string $message)
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
        return ['mail','database',SmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تأیید پیشنهاد {$this->proposal->id}")
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
