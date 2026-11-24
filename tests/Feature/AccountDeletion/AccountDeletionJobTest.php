<?php

namespace Tests\Feature\AccountDeletion;

use App\Http\Services\User\AccountDeletionService;
use App\Jobs\ProcessAccountDeletionJob;
use Mockery;

class AccountDeletionJobTest extends AccountDeletionTestCase
{
    public function test_job_calls_account_deletion_service_with_request_id(): void
    {
        $requestId = 123;

        $service = Mockery::mock(AccountDeletionService::class);

        $service->shouldReceive('processDeletionRequest')
            ->once()
            ->with($requestId)
            ->andReturn([
                'status' => true,
                'message' => 'Account deleted successfully.',
                'code' => 200,
            ]);

        $job = new ProcessAccountDeletionJob($requestId);

        $job->handle($service);
    }
}
