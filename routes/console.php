<?php

use App\Jobs\ProcessAccountDeletionJob;
use App\Models\User\AccountDeletionRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:dispatch-due-account-deletion-requests')
    ->everyMinute()
    ->withoutOverlapping();