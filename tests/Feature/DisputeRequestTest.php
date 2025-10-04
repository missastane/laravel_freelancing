<?php

namespace Tests\Feature;

use App\Events\AddDisputeRequestEvent;
use App\Models\Market\Conversation;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\Ticket\TicketDepartment;
use App\Models\Ticket\TicketPriority;
use App\Models\User\DisputeRequest;
use App\Models\User\User;
use App\Notifications\AddDisputeTicketNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DisputeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_store_dispute_request()
    {
        Event::fake();

        // Arrange
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'status' => 2, // in progress (قابل شکایت)
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'employee_id' => $freelancer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id,
            'status' => 1,
        ]);

        $payload = [
            'reason' => 'کار تحویل داده شده مطابق قرارداد نیست',
            'locked_reason' => 3,
        ];

        // Act
        $response = $this->actingAs($employer)->postJson(
            "/api/dispute-request/store/{$orderItem->id}",
            $payload
        );

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('dispute_requests', [
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'user_type' => 1, // employer
            'reason' => 'کار تحویل داده شده مطابق قرارداد نیست',
        ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'کار تحویل داده شده مطابق قرارداد نیست',
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 2, // closed
        ]);

        Event::assertDispatched(AddDisputeRequestEvent::class);
    }

    public function test_user_cannot_store_dispute_for_approved_item()
    {
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'status' => 4, // approved 
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'employee_id' => $freelancer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id,
            'status' => 1,
        ]);

        $payload = [
            'reason' => 'تست غیرمجاز',
            'locked_reason' => 3,
        ];

        $response = $this->actingAs($freelancer)->postJson(
            "/api/dispute-request/store/{$orderItem->id}",
            $payload
        );

        $response->assertStatus(403);
    }

    public function test_user_can_withdraw_his_dispute()
    {
        // Arrange
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'employee_id' => $freelancer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id,
            'status' => 2, // بسته
        ]);

        $disputeRequest = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'status' => 1, // open
            'reason' => 'initial lock',
        ]);

        // Act
        $response = $this->actingAs($employer)->patchJson(
            "/api/dispute-request/withdrawn/{$disputeRequest->id}"
        );

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('dispute_requests', [
            'id' => $disputeRequest->id,
            'status' => 3, // withdrawn
        ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'locked_by' => null,
            'locked_reason' => null,
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 1, // opend
        ]);
    }

    public function test_user_cannot_withdraw_other_users_dispute()
    {
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $disputeRequest = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'status' => 1, // open
            'reason' => 'initial lock',
        ]);
        $anotherUser = User::factory()->create();
        $response = $this->actingAs($anotherUser)->patchJson(
            "/api/dispute-request/withdrawn/{$disputeRequest->id}"
        );

        $response->assertStatus(403);
    }

    public function test_user_cannot_withdraw_closed_dispute()
    {
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $disputeRequest = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'status' => 2, // not open
            'reason' => 'initial lock',
        ]);
        $response = $this->actingAs($employer)->patchJson(
            "/api/dispute-request/withdrawn/{$disputeRequest->id}"
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_create_dispute_ticket()
    {
        Notification::fake();

        // Arrange
        $admin = User::factory()->admin()->create();
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $proposalMilestone = ProposalMilestone::factory()->create(['proposal_id' => Proposal::factory()->create()->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'proposal_milestone_id' => $proposalMilestone->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $disputeRequest = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'status' => 1,
            'reason' => 'initial lock',
        ]);


        $data = [
            'priority_id' => TicketPriority::factory()->create()->id,
            'department_id' => TicketDepartment::factory()->create()->id,
        ];

        // Act
        $response = $this->actingAs($admin)->postJson(
            "/api/admin/user/dispute-request/create-ticket/{$disputeRequest->id}",
            $data
        );

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $admin->id,
            'priority_id' => $data['priority_id'],
            'department_id' => $data['department_id'],
            'dispute_request_id' => $disputeRequest->id,
            'ticket_type' => 4,
            'subject' => "تیکت داوری مرحله {$proposalMilestone->title} سفارش {$order->id}",
        ]);

        Notification::assertSentTo([$employer, $freelancer], AddDisputeTicketNotification::class);
    }

    public function test_non_admin_cannot_create_dispute_ticket()
    {
        $user = User::factory()->create();
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();

        $order = Order::factory()->create([
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'status' => 2, // in progress
            'total_price' => 1000
        ]);

        $proposalMilestone = ProposalMilestone::factory()->create(['proposal_id' => Proposal::factory()->create()->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => 'initial lock',
            'locked_at' => now(),
            'proposal_milestone_id' => $proposalMilestone->id,
            'price' => $order->total_price,
            'freelancer_amount' => 900,
            'platform_fee' => 100
        ]);

        $disputeRequest = DisputeRequest::factory()->create([
            'order_item_id' => $orderItem->id,
            'raised_by' => $employer->id,
            'status' => 1,
            'reason' => 'initial lock',
        ]);

        $data = [
            'priority_id' => TicketPriority::factory()->create()->id,
            'department_id' => TicketDepartment::factory()->create()->id,
        ];
        $response = $this->actingAs($user)->postJson(
            "/api/admin/user/dispute-request/create-ticket/{$disputeRequest->id}",
            $data
        );

        $response->assertStatus(403);
    }
}
