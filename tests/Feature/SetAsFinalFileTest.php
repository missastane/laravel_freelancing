<?php

namespace Tests\Feature;

use App\Models\Market\Conversation;
use App\Models\Market\File;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\User\User;
use App\Notifications\SendFinalFileNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SetAsFinalFileTest extends TestCase
{
    use RefreshDatabase;

    protected User $freelancer;
    protected User $employer;
    protected Order $order;
    protected OrderItem $orderItem;
    protected Message $message;
    protected File $file;
    protected Project $project;
    protected Proposal $proposal;
    protected ProposalMilestone $proposalMilestone;
    protected Conversation $conversation;
    protected $freelancerAmunt;
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->freelancer = User::factory()->freelancer()->create();
        $this->employer = User::factory()->employer()->create();
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
            'status' => 1
        ]);
        $this->freelancerAmunt = ($this->proposalMilestone->amount * 90) / 100;
        $this->orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'status' => 2,
            'proposal_milestone_id' => $this->proposalMilestone->id,
            'price' => $this->proposalMilestone->amount,
            'freelancer_amount' => $this->freelancerAmunt,
            'platform_fee' => $this->proposalMilestone->amount - $this->freelancerAmunt
        ]);
        $this->conversation = Conversation::factory()->create([
            'employee_id' => $this->freelancer->id,
            'employer_id' => $this->employer->id,
        ]);
        $this->message = Message::factory()->create([
            'sender_id' => $this->employer->id,
            'conversation_id' => $this->conversation->id,
            'message' => 'hi baby',
            'message_context_id' => $this->order->id
        ]);
        $this->file =  File::factory()->create([
            'filable_id' => $this->message->id,
            'uploaded_by' => $this->freelancer->id
        ]);
    }
    public function test_freelancer_can_set_file_as_final()
    {
        Notification::fake();
        $file = $this->file;

        $this->actingAs($this->freelancer);

        $response = $this->postJson("/api/message/set-final-file/{$file->id}");

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'فایل با موفقیت برای کارفرما ارسال شد',
            ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'is_final_delivery' => 1,
        ]);

        $this->assertDatabaseHas('final_files', [
            'file_id' => $file->id,
            'freelancer_id' => $this->freelancer->id,
        ]);

        Notification::assertSentTo(
            $this->employer,
            SendFinalFileNotification::class
        );
    }
}
