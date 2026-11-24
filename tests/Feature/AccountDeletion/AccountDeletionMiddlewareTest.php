<?php

namespace Tests\Feature\AccountDeletion;

use App\Models\Market\Project;
use App\Models\Market\Subscription;
use App\Models\Payment\Wallet;
use App\Models\User\AccountDeletionRequest;
use App\Models\User\User;

class AccountDeletionMiddlewareTest extends AccountDeletionTestCase
{
     public function test_user_with_pending_deletion_cannot_create_project()
    {
        $user = User::factory()->employer()->create();

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/project/store');

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_restricted'),
            ]);
    }

    public function test_user_with_pending_deletion_cannot_start_payment()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payment');

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_restricted'),
            ]);
    }

    public function test_user_with_pending_deletion_cannot_request_withdrawal()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/withdrawal-request/add-request');

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_restricted'),
            ]);
    }

     public function test_user_with_pending_deletion_cannot_submit_proposal()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $project = Project::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/proposal/store/{$project->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_restricted'),
            ]);
    }

      public function test_user_with_pending_deletion_cannot_purchase_subscription()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $subscription = Subscription::create([
            'name' => 'Test Subscription',
            'amount' => 100000,
            'duration_days' => 30,
            'commission_rate' => 10,
            'max_target_per_month' => 10,
            'max_notification_per_month' => 100,
            'max_email_per_month' => 100,
            'max_sms_per_month' => 50,
            'max_view_deatils_per_month' => 100,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/subscription/purchase/{$subscription->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_restricted'),
            ]);
    }

     public function test_user_with_cancelled_deletion_request_is_not_restricted()
    {
        $user = User::factory()->employer()->create();

        $user->accountDeletionRequests()->create([
            'status' => AccountDeletionRequest::STATUS_CANCELLED,
            'scheduled_for' => now()->subDays(1),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/project/store');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

      public function test_user_with_completed_deletion_request_is_not_restricted()
    {
        $user = User::factory()->employer()->create();

        $user->accountDeletionRequests()->create([
            'status' => AccountDeletionRequest::STATUS_COMPLETED,
            'scheduled_for' => now()->subDays(8),
            'processed_at' => now(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/project/store');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_user_with_pending_deletion_can_view_profile()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/profile');

        $response->assertOk();
    }

     public function test_user_with_pending_deletion_can_view_orders()
    {
        $user = $this->authUser();

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/order');

        $response->assertOk();
    }

     public function test_user_with_pending_deletion_can_view_wallet()
    {
        $user = $this->authUser();

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $this->createPendingDeletionRequest($user);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/wallet');

        $response->assertOk();
    }
}