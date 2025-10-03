<?php

namespace Tests\Feature;

use App\Events\AddDisputeRequestEvent;
use App\Models\Market\Conversation;
use App\Models\Market\File;
use App\Models\Market\FinalFile;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\Payment\Wallet;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FinalFileTest extends TestCase
{
    use RefreshDatabase;

    protected User $freelancer;
    protected User $employer;
    protected Wallet $freelancerWallet;
    protected Wallet $employerWallet;
    protected Order $order;
    protected OrderItem $orderItem;
    protected Message $message;
    protected File $file;
    protected Project $project;
    protected Proposal $proposal;
    protected ProposalMilestone $proposalMilestone;
    protected Conversation $conversation;
    protected $freelancerAmunt;
    protected FinalFile $finalFile;
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->freelancer = User::factory()->freelancer()->create();
        $this->employer = User::factory()->employer()->create();
        $this->freelancerWallet = Wallet::factory()->create([
            'user_id' => $this->freelancer->id,
            'balance' => 0
        ]);
        $this->employerWallet = Wallet::factory()->create([
            'user_id' => $this->employer->id,
            'balance' => 10000,
            'locked_balance' => 5000
        ]);
        $this->project = Project::factory()->create(['user_id' => $this->employer->id]);
        $this->proposal = Proposal::factory()->create([
            'project_id' => $this->project->id,
            'freelancer_id' => $this->freelancer->id,
            'description' => 'پیشنهاد تستی برای پروژه',
            'status' => 2,
            'total_amount' => 5000
        ]);
        $this->proposalMilestone = ProposalMilestone::factory()->create(['proposal_id' => $this->proposal->id]);
        $this->order = Order::factory()->create([
            'proposal_id' => $this->proposal->id,
            'project_id' => $this->project->id,
            'employer_id' => $this->employer->id,
            'freelancer_id' => $this->freelancer->id,
            'total_price' => 5000,
            'status' => 2
        ]);
        $this->orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'status' => 3,
            'proposal_milestone_id' => $this->proposalMilestone->id,
            'price' => 5000,
            'freelancer_amount' => 4500,
            'platform_fee' => 500
        ]);
        $this->conversation = Conversation::factory()->create([
            'employee_id' => $this->freelancer->id,
            'employer_id' => $this->employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $this->order->id
        ]);
        $this->message = Message::factory()->create([
            'sender_id' => $this->employer->id,
            'conversation_id' => $this->conversation->id,
            'message' => 'hi baby',
            'message_context_id' => $this->order->id
        ]);
        $this->file = File::factory()->create([
            'filable_id' => $this->message->id,
            'uploaded_by' => $this->freelancer->id
        ]);
        $this->finalFile = FinalFile::factory()->create([
            'order_item_id' => $this->orderItem->id,
            'file_id' => $this->file->id,
            'freelancer_id' => $this->freelancer->id,
            'status' => 1
        ]);
    }
    public function test_main_employer_can_approve_final_file_item()
    {
        $this->actingAs($this->employer);

        $response = $this->putJson("/api/final-file/approve/{$this->finalFile->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'این مرحله با موفقیت تایید و مبلغ آن برای فریلنسر آزاد شد',
            ]);

        // approve final file
        $this->assertDatabaseHas('final_files', [
            'id' => $this->finalFile->id,
            'status' => 2,
            'employer_id' => $this->employer->id,
        ]);

        // approve order item
        $this->assertDatabaseHas('order_items', [
            'id' => $this->orderItem->id,
            'status' => 4,
        ]);

        // decrease employer wallet
        $this->assertDatabaseHas('wallets', [
            'id' => $this->employerWallet->id,
            'balance' => 5000,
            'locked_balance' => 0,
        ]);

        // increase freelancer wallet
        $this->assertDatabaseHas('wallets', [
            'id' => $this->freelancerWallet->id,
            'balance' => 4500,
        ]);
    }

    public function test_other_user_cannot_approve_final_file()
    {
        $other = User::factory()->create();
        $this->actingAs($other);

        $response = $this->putJson("/api/final-file/approve/{$this->finalFile->id}");

        $response->assertStatus(403);
    }

    public function test_employer_cannot_approve_if_order_item_status_is_not_completed()
    {
        $this->orderItem->update(['status' => 2]); // in progress

        $this->actingAs($this->employer);

        $response = $this->putJson("/api/final-file/approve/{$this->finalFile->id}");

        $response->assertStatus(403);
    }


    public function test_employer_can_reject_final_file()
    {
        Event::fake();

        $payload = [
            'rejected_note' => 'این فایل مشکل دارد',
        ];

        // Act
        $response = $this->actingAs($this->employer)
            ->putJson("/api/final-file/reject/{$this->finalFile->id}", $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'فایل سفارش توسط کارفرما قفل شد و اختلاف نظر ثبت شد. لطفا منتظر نتیجه داوری بمانید',
                'data' => null
            ]);

        // FinalFile should be updated
        $this->assertDatabaseHas('final_files', [
            'id' => $this->finalFile->id,
            'status' => 4,
            'rejected_note' => 'این فایل مشکل دارد',
        ]);

        // OrderItem should be locked
        $this->assertDatabaseHas('order_items', [
            'id' => $this->orderItem->id,
            'status' => 5,
            'locked_reason' => 3,
            'locked_note' => 'این فایل مشکل دارد',
        ]);

        // Conversation should be locked
        $this->assertDatabaseHas('conversations', [
            'id' => $this->conversation->id,
            'status' => 2,
        ]);

        // Dispute request should be created
        $this->assertDatabaseHas('dispute_requests', [
            'order_item_id' => $this->orderItem->id,
            'final_file_id' => $this->finalFile->id,
            'reason' => 'این فایل مشکل دارد',
            'raised_by' => $this->employer->id,
        ]);

        // Event dispatched
        Event::assertDispatched(AddDisputeRequestEvent::class);
    }

     public function test_employer_cannot_reject_if_final_file_status_is_not_pending()
    {
        $this->finalFile->update(['status' => 2]); // approved

        $this->actingAs($this->employer);

        $payload = [
            'rejected_note' => 'این فایل مشکل دارد',
        ];
        $response = $this->putJson("/api/final-file/reject/{$this->finalFile->id}", $payload);

        $response->assertStatus(403);
    }

    public function test_employer_can_send_final_file_to_revision()
    {
        $data = [
            'revision_note' => 'این فایل همونی نیست که میخواستم'
        ];

        $response = $this->actingAs($this->employer)
            ->putJson("/api/final-file/revision/{$this->finalFile->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'فایل با موفقیت جهت بازبینی فریلنسر ارجاع شد',
                'data' => null
            ]);

        $this->assertDatabaseHas('final_files', [
            'id' => $this->finalFile->id,
            'status' => 3,
            'revision_note' => 'این فایل همونی نیست که میخواستم',
        ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $this->orderItem->id,
            'status' => 2,
        ]);
    }

     public function test_employer_cannot_send_to_revision_if_final_file_status_is_not_pending()
    {
        $this->finalFile->update(['status' => 2]); // approved

        $this->actingAs($this->employer);

        $payload = [
            'revision_note' => 'این فایل همونی نیست که میخواستم',
        ];
        $response = $this->putJson("/api/final-file/revision/{$this->finalFile->id}", $payload);

        $response->assertStatus(403);
    }

}
