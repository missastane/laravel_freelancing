<?php

namespace Tests\Feature\AccountDeletion;

use App\Models\User\User;
use App\Models\User\AccountDeletionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class AccountDeletionTestCase extends TestCase
{
    use RefreshDatabase;

    protected function authUser(): User
    {
        return User::factory()->freelancer()->create([
            'password' => Hash::make('password123'),
        ]);
    }

    protected function createPendingDeletionRequest(
        User $user
    ): AccountDeletionRequest {
        return $user->accountDeletionRequests()->create([
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'scheduled_for' => now()->addDays(7),
        ]);
    }

    protected function assertDeletionBlocked(User $user): void
    {
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'password123',
            ]);

        $response->assertStatus(409);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    protected function assertDeletionAllowed(User $user): void
    {
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => __('messages.account_deletion.request_created'),
            ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);
    }
}
