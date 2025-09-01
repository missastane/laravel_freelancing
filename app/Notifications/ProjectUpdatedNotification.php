<?php

namespace App\Notifications;

use App\Models\Market\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected Project $project;
    public function __construct(Project $project)
    {
        $this->project = $project;
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
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        date_default_timezone_set('Asia/Tehran');
        return [
            'message' => "کارفرما پروژه '{$this->project->title}' را بروزرسانی کرده است.",
            'created_at' => now(),
        ];
    }
}
