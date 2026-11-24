<?php

namespace Tests\Feature\AccountDeletion;

use App\Models\User\User;
use App\Models\User\DisputeRequest;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\Subscription;
use App\Models\Payment\Payment;
use App\Models\Payment\Wallet;
use App\Models\User\AccountDeletionRequest;
use Illuminate\Support\Facades\Hash;

class AccountDeletionRequestTest extends AccountDeletionTestCase
{
    public function test_admin_cannot_request_account_deletion()
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'password123',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.admin_not_allowed'),
            ]);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_can_request_account_deletion()
    {
        $user = $this->authUser();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'password123',
                'reason' => 'I no longer need this account.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => __('messages.account_deletion.request_created'),
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'reason',
                    'scheduled_for',
                ],
            ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'reason' => 'I no longer need this account.',
        ]);

        $deletionRequest = $user->accountDeletionRequests()->first();

        $this->assertNotNull($deletionRequest);
        $this->assertTrue(
            $deletionRequest->scheduled_for->between(
                now()->addDays(7)->subMinute(),
                now()->addDays(7)->addMinute()
            )
        );
    }

    public function test_guest_cannot_request_account_deletion()
    {
        $response = $this->postJson(
            '/api/profile/account-deletion-requests',
            [
                'password' => 'password123',
            ]
        );

        $response->assertStatus(401);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_cannot_request_account_deletion_with_wrong_password()
    {
        $user = $this->authUser();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.invalid_password'),
            ]);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_cannot_create_another_pending_deletion_request()
    {
        $user = $this->authUser();

        $user->accountDeletionRequests()->create([
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'scheduled_for' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'password' => 'password123',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.account_deletion.pending_request_exists'),
            ]);

        $this->assertDatabaseCount('account_deletion_requests', 1);
    }

    public function test_password_is_required_to_request_account_deletion()
    {
        $user = $this->authUser();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/profile/account-deletion-requests', [
                'reason' => 'I no longer need this account.',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_user_cannot_request_deletion_with_active_order()
    {
        $user = $this->authUser();

        Order::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_user_cannot_request_deletion_with_pending_dispute()
    {
        $user = $this->authUser();

        $order = Order::factory()->create([
            'freelancer_id' => $user->id,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $user->id,
            'status' => DisputeRequest::STATUS_PENDING,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_employer_cannot_request_deletion_with_active_project()
    {
        $user = User::factory()->employer()->create([
            'password' => Hash::make('password123'),
        ]);

        Project::factory()->create([
            'user_id' => $user->id,
            'status' => Project::STATUS_PENDING,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_freelancer_cannot_request_deletion_with_pending_proposal()
    {
        $user = $this->authUser();

        Proposal::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Proposal::STATUS_PENDING,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_user_cannot_request_deletion_with_positive_wallet_balance()
    {
        $user = $this->authUser();

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
            'locked_balance' => 0,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_user_cannot_request_deletion_with_negative_wallet_balance()
    {
        $user = $this->authUser();

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => -1000,
            'locked_balance' => 0,
        ]);

        $this->assertDeletionBlocked($user);
    }

     public function test_user_cannot_request_deletion_with_locked_wallet_balance()
    {
        $user = $this->authUser();

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 1000,
        ]);

        $this->assertDeletionBlocked($user);
    }

     public function test_user_cannot_request_deletion_with_pending_payment()
    {
        $user = $this->authUser();

        Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertDeletionBlocked($user);
    }

    public function test_zero_wallet_balance_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_paid_payment_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_failed_payment_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => Payment::STATUS_NOT_PAID,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_returned_payment_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Payment::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => Payment::STATUS_RETURNED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_completed_order_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Order::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Order::STATUS_COMPLETED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_cancelled_order_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Order::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Order::STATUS_CANCELLED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_completed_project_does_not_block_account_deletion()
    {
        $user = User::factory()->employer()->create([
            'password' => Hash::make('password123'),
        ]);

        Project::factory()->create([
            'user_id' => $user->id,
            'status' => Project::STATUS_COMPLETED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_cancelled_project_does_not_block_account_deletion()
    {
        $user = User::factory()->employer()->create([
            'password' => Hash::make('password123'),
        ]);

        Project::factory()->create([
            'user_id' => $user->id,
            'status' => Project::STATUS_CANCELLED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_rejected_proposal_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Proposal::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Proposal::STATUS_REJECTED,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_withdrawn_proposal_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        Proposal::factory()->create([
            'freelancer_id' => $user->id,
            'status' => Proposal::STATUS_WITHDRAWN,
        ]);

        $this->assertDeletionAllowed($user);
    }

     public function test_resolved_dispute_does_not_block_account_deletion()
    {
        $user = $this->authUser();

        $order = Order::factory()->create([
            'freelancer_id' => $user->id,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $user->id,
            'status' => DisputeRequest::STATUS_RESOLVED,
        ]);

        $this->assertDeletionAllowed($user);
    }
}