<?php

namespace App\Jobs;

use App\Http\Services\User\AccountDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessAccountDeletionJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public int $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return 'account-deletion-' . $this->requestId;
    }

    public function __construct(
        public int $requestId
    ) {
    }

    public function handle(AccountDeletionService $accountDeletionService): void
    {
        $accountDeletionService->processDeletionRequest($this->requestId);
    }
}