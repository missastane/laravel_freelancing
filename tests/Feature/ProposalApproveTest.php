<?php

namespace Tests\Feature;

use App\Exceptions\Market\NotEnoughBalanceException;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\Payment\Wallet;
use App\Models\User\User;
use App\Notifications\ApproveProposalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Http\Services\Proposal\ProposalApprovalService;
class ProposalApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_project_owner_can_approve_proposal()
    {
        $employer = User::factory()->employer()->create();
        $employerWallet = Wallet::factory()->create([
            'user_id' => $employer->id,
            'balance' => 0,
        ]);
        $proposal = Proposal::factory()
            ->for(Project::factory()->create(['user_id' => $employer->id]), 'project')
            ->for(User::factory()->freelancer(), 'freelancer')
            ->create(['status' => 1, 'description' => 'پیشنهاد تستی برای پروژه']);

        $otherUser = User::factory()->employer()->create();
        $otherUserWallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'balance' => 0,
        ]);
        $this->actingAs($otherUser, 'api')
            ->postJson("/api/proposal/approve/{$proposal->id}")
            ->assertStatus(403);
    }

    public function test_it_throws_exception_if_wallet_has_not_enough_balance()
    {
        $employer = User::factory()->create();
        $wallet = Wallet::factory()->for($employer)->create([
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $proposal = Proposal::factory()
            ->for(Project::factory()->for($employer, 'employer'), 'project')
            ->for(User::factory(), 'freelancer')
            ->create([
                'status' => 1,
                'total_amount' => 5000,
                'description' => 'پیشنهاد تستی برای پروژه'
            ]);

        $this->actingAs($employer, 'api');

        $this->expectException(NotEnoughBalanceException::class);

        app(ProposalApprovalService::class)->approveProposal($proposal);
    }

    public function test_main_employer_can_approve_proposal_successfully()
    {
        Notification::fake();

        $employer = User::factory()->employer()->create();
        $wallet = Wallet::factory()->for($employer)->create([
            'balance' => 10000,
            'locked_balance' => 0,
        ]);

        $freelancer = User::factory()->create();

        $project = Project::factory()->for($employer, 'employer')->create();

        $proposal = Proposal::factory()
            ->for($project, 'project')
            ->for($freelancer, 'freelancer')
            ->create([
                'status' => 1,
                'total_amount' => 5000,
                'description' => 'پیشنهاد تستی برای پروژه'
            ]);

        // ایجاد milestone برای تست order item
        ProposalMilestone::factory()->for($proposal, 'proposal')->create([
            'title' => 'مرحله اول',
            'description' => 'انجام بخش اول پروژه',
            'amount' => 5000,
            'duration_time' => 5,
            'due_date' => now()->addDays(5)
        ]);

        $this->actingAs($employer, 'api')
            ->postJson("/api/proposal/approve/{$proposal->id}")
            ->assertStatus(201)
            ->assertJson(['message' => 'پیشنهاد با موفقیت پذیرفته شد']);

        // پروپوزال باید در حال انجام باشد
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 2
        ]);

        // پروژه باید in progress شده باشد
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 2
        ]);

        // سفارش ایجاد شده باشد
        $this->assertDatabaseHas('orders', [
            'proposal_id' => $proposal->id,
            'freelancer_id' => $freelancer->id,
            'employer_id' => $employer->id,
            'total_price' => 5000,
            'status' => 2
        ]);

        // آیتم سفارش ثبت شده باشد
        $this->assertDatabaseHas('order_items', [
            'price' => 5000,
            'order_id' => 1
        ]);

        // موجودی بلوکه شده آپدیت شده باشد
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'locked_balance' => 5000
        ]);

        // تراکنش ثبت شده باشد
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => 5000,
            'transaction_type' => 3
        ]);

        // نوتیفیکیشن ارسال شده باشد
        Notification::assertSentTo($freelancer, ApproveProposalNotification::class);
    }





}
