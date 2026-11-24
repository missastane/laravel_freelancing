<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAccountDeletionJob;
use App\Models\User\AccountDeletionRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:dispatch-due-account-deletion-requests')]
#[Description('Dispatch due account deletion requests for processing.')]
class DispatchDueAccountDeletionRequests extends Command
{
    public function handle(): int
    {
        $dispatchedCount = 0;

        AccountDeletionRequest::query()
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->where('scheduled_for', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($requests) use (&$dispatchedCount) {
                foreach ($requests as $request) {
                    ProcessAccountDeletionJob::dispatch($request->id);

                    $dispatchedCount++;
                }
            });

        $this->info("{$dispatchedCount} account deletion job(s) dispatched.");

        return self::SUCCESS;
    }
}