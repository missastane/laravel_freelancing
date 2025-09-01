<?php

namespace App\Notifications;

use App\Models\Market\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Proposal $proposal)
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
        return ['database'];
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
            'message' => "فریلنسر پیشنهاد خود را روی پروژه '{$this->proposal->project->title}' را بروزرسانی کرده است.",
            'created_at' => now(),
        ];
    }
}
