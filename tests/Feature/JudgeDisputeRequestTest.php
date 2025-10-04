<?php

namespace Tests\Feature;

use App\Models\Market\Conversation;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\User\DisputeRequest;
use App\Models\User\User;
use App\Notifications\JudgeResultNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JudgeDisputeRequestTest extends TestCase
{
    use RefreshDatabase;

    private function setupDisputeEnvironment()
    {
        $admin = User::factory()->admin()->create();
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        // کیف پول
        $employer->wallet()->create(['balance' => 1000, 'locked_balance' => 1000]);
        $freelancer->wallet()->create(['balance' => 0, 'locked' => 0]);

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $proposal = Proposal::factory()->create([
            'project_id' => Project::factory()->create(['user_id' => $employer->id]),
            'freelancer_id' => $freelancer->id,
            'description' => 'پیشنهاد تستی برای پروژه',
            'status' => 2,
            'total_amount' => 5000
        ]);

        $milestone = ProposalMilestone::factory()->create([
            'proposal_id' => $proposal->id,
            'title' => 'مرحله طراحی',
            'amount' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'proposal_milestone_id' => $milestone->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'price' => 1000,
            'status' => 2, // in progress
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $dispute = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'status' => 1, // open
            'raised_by' => $employer->id,
            'reason' => 'initial lock',
        ]);

        $conversation = Conversation::factory()->create([
            'employee_id' => $freelancer->id,
            'employer_id' => $employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id
        ]);
        return [$admin, $employer, $freelancer, $order, $orderItem, $dispute, $conversation];
    }

    public function test_admin_can_judge_in_favor_of_employer()
    {
        Notification::fake();
        [$admin, $employer, $freelancer, $order, $orderItem, $dispute] = $this->setupDisputeEnvironment();

        $data = [
            'status' => 2,
            'freelancer_percent' => null,
            'employer_percent' => null,
            'description' => 'به نفع کارفرما',
        ];

        $response = $this->actingAs($admin)->postJson(
            "/api/admin/user/dispute-request/judge/{$dispute->id}",
            $data
        );

        $response->assertOk();
        $this->assertDatabaseHas('arbitration_requests', [
            'dispute_request_id' => $dispute->id,
            'status' => 2,
        ]);
        $this->assertDatabaseHas('dispute_requests', [
            'id' => $dispute->id,
            'status' => 2
        ]);

        Notification::assertSentTo([$employer, $freelancer], JudgeResultNotification::class);
    }

    public function test_admin_can_judge_in_favor_of_freelancer()
    {
        Notification::fake();
        [$admin, $employer, $freelancer, $order, $orderItem, $dispute] = $this->setupDisputeEnvironment();

        $data = [
            'status' => 3,
            'freelancer_percent' => null,
            'employer_percent' => null,
            'description' => 'به نفع فریلنسر',
        ];

        $response = $this->actingAs($admin)->postJson(
            "/api/admin/user/dispute-request/judge/{$dispute->id}",
            $data
        );

        $response->assertOk();
        $this->assertDatabaseHas('arbitration_requests', [
            'dispute_request_id' => $dispute->id,
            'status' => 3
        ]);

        Notification::assertSentTo([$employer, $freelancer], JudgeResultNotification::class);
    }

    public function test_admin_can_distribute_money()
    {
        Notification::fake();
        [$admin, $employer, $freelancer, $order, $orderItem, $dispute] = $this->setupDisputeEnvironment();

        $data = [
            'status' => 4,
            'freelancer_percent' => 60,
            'employer_percent' => 40,
            'description' => 'تقسیم پول',
        ];

        $response = $this->actingAs($admin)->postJson(
            "/api/admin/user/dispute-request/judge/{$dispute->id}",
            $data
        );

        $response->assertOk();
        $this->assertDatabaseHas('arbitration_requests', [
            'dispute_request_id' => $dispute->id,
            'status' => 4,
        ]);
        Notification::assertSentTo([$employer, $freelancer], JudgeResultNotification::class);
    }


    public function test_admin_can_set_no_change()
    {
        Notification::fake();
        [$admin, $employer, $freelancer, $order, $orderItem, $dispute, $conversation] = $this->setupDisputeEnvironment();

        $data = [
            'status' => 5,
            'freelancer_percent' => null,
            'employer_percent' => null,
            'description' => 'بدون تغییر',
        ];

        $response = $this->actingAs($admin)->postJson(
            "/api/admin/user/dispute-request/judge/{$dispute->id}",
            $data
        );

        $response->assertOk();
        $this->assertDatabaseHas('arbitration_requests', [
            'dispute_request_id' => $dispute->id,
            'status' => 5,
        ]);
        $this->assertDatabaseHas('dispute_requests', [
            'id' => $dispute->id,
            'status' => 3 // rejected
        ]);

        Notification::assertSentTo([$employer, $freelancer], JudgeResultNotification::class);
    }

    public function test_non_admin_cannot_judge()
    {
        $user = User::factory()->create();
        [$admin, $employer, $freelancer, $order, $orderItem, $dispute, $conversation] = $this->setupDisputeEnvironment();

        $response = $this->actingAs($user)->postJson(
            "/api/admin/user/dispute-request/judge/{$dispute->id}",
            ['status' => 2, 'description' => 'بدون تغییر']
        );

        $response->assertStatus(403);
    }
}
