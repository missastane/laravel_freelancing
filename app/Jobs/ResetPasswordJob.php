<?php

namespace App\Jobs;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResetPasswordJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user, protected $token)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new ResetPasswordNotification($this->token, ['message' => 'لینک بازیابی کلمه عبور بنابر درخواست کاربر به آدرس  ' . $this->user->email . ' با موفقیت ارسال شد']));

    }
}
