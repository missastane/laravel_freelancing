<?php

namespace App\Jobs;

use App\Models\Market\Project;
use App\Notifications\ProjectUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendNotificationForUpdatingProject implements ShouldQueue
{
    use Queueable;

    protected Collection $users;
    protected Project $project;
    /**
     * Create a new job instance.
     */

    public function __construct(Collection $users, Project $project)
    {
        $this->users = $users;
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::send($this->users,new ProjectUpdatedNotification($this->project));
    }
}
