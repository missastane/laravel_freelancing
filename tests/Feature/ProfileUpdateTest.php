<?php

namespace Tests\Feature;

use App\Events\AddDisputeRequestEvent;
use App\Exceptions\Market\NotEnoughBalanceException;
use App\Exceptions\User\WrongCurrentPasswordException;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Market\Conversation;
use App\Models\Market\File;
use App\Models\Market\FinalFile;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Project;
use App\Models\Market\ProjectCategory;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\Market\Skill;
use App\Models\Payment\Wallet;
use App\Models\Ticket\TicketDepartment;
use App\Models\Ticket\TicketPriority;
use App\Models\User\DisputeRequest;
use App\Models\User\OTP;
use App\Models\User\User;
use App\Notifications\AddDisputeTicketNotification;
use App\Notifications\ApproveProposalNotification;
use App\Notifications\WithdrawProposalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use function PHPUnit\Framework\assertJson;
use App\Http\Services\Proposal\ProposalApprovalService;

class ProfileUpdateTest extends TestCase
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


  

}
