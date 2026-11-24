<?php

namespace Tests\Feature\AccountDeletion;

use App\Jobs\ProcessAccountDeletionJob;
use App\Models\User\AccountDeletionRequest;
use Illuminate\Support\Facades\Queue;

class AccountDeletionCommandTest extends AccountDeletionTestCase
{
    public function test_command_dispatches_only_due_pending_deletion_requests(): void
    {
        Queue::fake();

        // Due pending request
        $dueUser = $this->authUser();

        $dueRequest = $this->createPendingDeletionRequest($dueUser);
        $dueRequest->update([
            'scheduled_for' => now()->subMinute(),
        ]);

        // Future pending request
        $futureUser = $this->authUser();

        $futureRequest = $this->createPendingDeletionRequest($futureUser);

        // Cancelled request
        $cancelledUser = $this->authUser();

        $cancelledRequest = $this->createPendingDeletionRequest($cancelledUser);
        $cancelledRequest->update([
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
            'scheduled_for' => now()->subMinute(),
        ]);

        // Completed request
        $completedUser = $this->authUser();

        $completedRequest = $this->createPendingDeletionRequest($completedUser);
        $completedRequest->update([
            'status' => AccountDeletionRequest::STATUS_COMPLETED,
            'scheduled_for' => now()->subMinute(),
        ]);

        $this->artisan('app:dispatch-due-account-deletion-requests')
            ->assertSuccessful();

        Queue::assertPushed(ProcessAccountDeletionJob::class, 1);

        Queue::assertPushed(
            ProcessAccountDeletionJob::class,
            fn (ProcessAccountDeletionJob $job) =>
                $job->requestId === $dueRequest->id
        );

        Queue::assertNotPushed(
            ProcessAccountDeletionJob::class,
            fn (ProcessAccountDeletionJob $job) =>
                $job->requestId !== $dueRequest->id
        );
    }

    public function test_command_does_not_dispatch_jobs_when_no_due_requests_exist(): void
    {
        Queue::fake();

        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $this->artisan('app:dispatch-due-account-deletion-requests')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }
}
