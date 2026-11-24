<?php

namespace Tests\Feature\AccountDeletion;

use App\Models\User\AccountDeletionRequest;
use App\Models\User\User;
use Illuminate\Support\Facades\Hash;

class AccountDeletionCancellationTest extends AccountDeletionTestCase
{
    public function test_user_can_cancel_pending_account_deletion_request()
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => __('messages.account_deletion.request_cancelled'),
            ])
            ->assertJsonPath(
                'data.id',
                $deletionRequest->id
            )
            ->assertJsonPath(
                'data.status',
                AccountDeletionRequest::STATUS_CANCELLED
            );

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'user_id' => $user->id,
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
        ]);
    }

    public function test_user_cannot_cancel_without_pending_deletion_request()
    {
        $user = $this->authUser();

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.no_pending_request'),
            ]);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_cannot_cancel_deletion_request_after_grace_period()
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);

        $deletionRequest->update([
            'scheduled_for' => now()->subSecond(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.cancellation_period_expired'),
            ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);
    }

    public function test_user_cannot_cancel_another_users_deletion_request()
    {
        $firstUser = $this->authUser();
        $secondUser = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($firstUser);

        $response = $this->actingAs($secondUser, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.no_pending_request'),
            ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'user_id' => $firstUser->id,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);
    }

    public function test_guest_cannot_cancel_account_deletion_request()
    {
        $response = $this->patchJson(
            '/api/profile/account-deletion-requests/cancel'
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_can_create_project_after_cancelling_deletion_request()
    {
        $user = User::factory()->employer()->create([
            'password' => Hash::make('password123'),
        ]);

        $deletionRequest = $this->createPendingDeletionRequest($user);

        $cancelResponse = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $cancelResponse->assertOk();

        $projectResponse = $this->actingAs($user, 'api')
            ->postJson('/api/project/store');

        $this->assertNotEquals(403, $projectResponse->getStatusCode());

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
        ]);
    }

    public function test_user_cannot_exceed_account_deletion_cancellation_rate_limit()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        // First request: successfully cancels the pending request.
        $firstResponse = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $firstResponse->assertOk();

        // Second to fifth requests: counted, but no pending request exists.
        for ($i = 0; $i < 4; $i++) {
            $response = $this->actingAs($user, 'api')
                ->patchJson('/api/profile/account-deletion-requests/cancel');

            $response->assertStatus(409);
        }

        // Sixth request: rate limit exceeded.
        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/profile/account-deletion-requests/cancel');

        $response->assertStatus(429);

        $this->assertDatabaseCount('account_deletion_requests', 1);
        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
        ]);
    }
}