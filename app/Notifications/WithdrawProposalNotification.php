<?php

namespace App\Notifications;

use App\Models\Market\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawProposalNotification extends Notification implements ShouldQueue
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
        return ['mail'];
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
            'message' => "فریلنسر گرامی، پیشنهاد شما روی پروژه '{$this->proposal->project->title} با موفقیت پس گرفته شد",
            'created_at' => now(),
        ];
    }
}
