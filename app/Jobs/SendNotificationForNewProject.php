<?php

namespace App\Jobs;

use App\Notifications\AddNewProject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendNotificationForNewProject implements ShouldQueue
{
    use Queueable;
    protected Collection $users;
    /**
     * Create a new job instance.
     */
    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::send($this->users, new AddNewProject( "پروژه جدید ثبت شد"));
    }
}
