<?php

namespace Tests\Feature\AccountDeletion;

use App\Http\Services\User\AccountDeletionService;
use App\Models\Payment\Payment;
use App\Models\User\AccountDeletionRequest;
use App\Models\User\AuthSession;
use App\Models\User\RefreshToken;
use Illuminate\Support\Facades\Auth;

class AccountDeletionProcessingTest extends AccountDeletionTestCase
{
    public function test_deletion_request_cannot_be_processed_before_scheduled_time(): void
    {
        $user = $this->authUser();
        $deletionRequest = $this->createPendingDeletionRequest($user);

        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);
    }

    public function test_eligible_deletion_request_soft_deletes_user_and_revokes_sessions_and_refresh_tokens(): void
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);
        $deletionRequest->update([
            'scheduled_for' => now()->subMinute(),
        ]);

        $session = AuthSession::query()->create([
            'user_id' => $user->id,
            'client_type' => 'web',
            'remember_me' => true,
            'expires_at' => now()->addDays(30),
        ]);

        $refreshToken = $session->refreshTokens()->create([
            'token_hash' => hash('sha256', 'test-refresh-token'),
            'expires_at' => now()->addDays(30),
        ]);

        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertTrue($result['status']);
        $this->assertSame(200, $result['code']);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_COMPLETED,
        ]);

        $this->assertNotNull($session->fresh()->revoked_at);
        $this->assertNotNull($refreshToken->fresh()->revoked_at);
    }

    public function test_deletion_fails_if_a_pending_payment_exists(): void
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);
        $deletionRequest->update([
            'scheduled_for' => now()->subMinute(),
        ]);

        Payment::query()->create([
            'user_id' => $user->id,
            'amount' => 5000,
            'description' => 'Test pending payment',
            'status' => Payment::STATUS_PENDING,
        ]);

        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_FAILED,
        ]);

        $updatedRequest = $deletionRequest->fresh();

        $this->assertNotNull($updatedRequest->processed_at);
        $this->assertStringContainsString(
            __('errors.account_deletion.blockers.pending_payments'),
            $updatedRequest->failure_reason
        );
    }

    public function test_cancelled_deletion_request_cannot_be_processed(): void
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);
        $deletionRequest->update([
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
            'scheduled_for' => now()->subMinute(),
        ]);

        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $deletionRequest->id,
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
        ]);
    }

    public function test_completed_deletion_request_cannot_be_processed_again(): void
    {
        $user = $this->authUser();

        $deletionRequest = $this->createPendingDeletionRequest($user);
        $deletionRequest->update([
            'status' => AccountDeletionRequest::STATUS_COMPLETED,
            'scheduled_for' => now()->subMinute(),
        ]);

        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_nonexistent_deletion_request_returns_not_found(): void
    {
        $result = app(AccountDeletionService::class)
            ->processDeletionRequest(999999);

        $this->assertFalse($result['status']);
        $this->assertSame(404, $result['code']);
    }

    public function test_deleted_user_cannot_access_protected_routes_with_old_access_token(): void
    {
        $user = $this->authUser();

        // Login and obtain a valid access token.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $accessToken = $loginResponse->json('data.access_token');

        $this->assertNotEmpty($accessToken);

        // Verify that the access token works before account deletion.
        $this->withToken($accessToken)
            ->getJson('/api/profile')
            ->assertStatus(200);

        // Create a due account deletion request.
        $deletionRequest = $this->createPendingDeletionRequest($user);

        $deletionRequest->update([
            'scheduled_for' => now()->subMinute(),
        ]);

        // Process account deletion.
        $result = app(AccountDeletionService::class)
            ->processDeletionRequest($deletionRequest->id);

        $this->assertTrue($result['status']);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        // Reset cached authentication guards to simulate a fresh HTTP request.
        Auth::forgetGuards();

        // The old access token must no longer authorize API requests.
        $this->withToken($accessToken)
            ->getJson('/api/profile')
            ->assertStatus(401);
    }
}
