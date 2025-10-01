<?php

namespace Tests\Feature;

use App\Http\Services\Notification\SubscriptionUsageManagerService;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Payment\Wallet;
use App\Models\User\User;
use App\Notifications\AddNewProposal;
use App\Notifications\ProposalUpdatedNotification;
use App\Notifications\WithdrawProposalNotification;
use App\Repositories\Contracts\Market\ProposalMilestoneRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_proposal_success()
    {
        $this->withExceptionHandling();

        // 1. ایجاد کارفرما و پروژه
        $employer = User::factory()->freelancer()->create();

        $project = Project::factory()->create([
            'user_id' => $employer->id,
        ]);

        // 2. ایجاد فریلنسر
        $freelancer = User::factory()->freelancer()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $freelancer->id,
            'balance' => 0,
        ]);
        // 3. داده‌های مایلستون و پروپوزال
        $data = [
            'description' => 'پیشنهاد تستی برای پروژه',
            'milestones' => [
                ['title' => 'مرحله 1', 'description' => 'توضیح مرحله 1', 'amount' => 500000, 'duration_time' => 3],
                ['title' => 'مرحله 2', 'description' => 'توضیح مرحله 2', 'amount' => 700000, 'duration_time' => 5],
            ],
        ];

        // 4. Mock کردن repository ها و سرویس اشتراک
        $this->mock(ProposalMilestoneRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('existsForProjectAndFreelancer')->andReturn(false);
            $mock->shouldReceive('create')->andReturnUsing(function ($attrs) {
                return Proposal::factory()->make($attrs);
            });
            $mock->shouldReceive('update')->andReturnTrue();
        });

        $this->mock(ProposalMilestoneRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('create')->andReturnTrue();
        });

        $this->mock(SubscriptionUsageManagerService::class, function ($mock) use ($freelancer) {
            $mock->shouldReceive('canUse')->with('target_create')->andReturn(true);
            $mock->shouldReceive('increamentUsage')->andReturnTrue();
        });

        Notification::fake();

        // 5. ارسال درخواست API
        $response = $this->actingAs($freelancer, 'api')
            ->postJson("/api/proposal/store/{$project->id}", $data);

        // 6. بررسی response
        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'پیشنهاد با موفقیت ثبت شد'
            ]);

        // 7. بررسی اینکه نوتیفیکیشن ارسال شده
        Notification::assertSentTo(
            [$employer],
            AddNewProposal::class
        );
    }


    public function test_user_cannot_update_proposal_if_not_authorized()
    {
        $oldUser = User::factory()->create();
        $proposal = Proposal::factory()->create([
            'status' => 1,
            'freelancer_id' => $oldUser->id,
            'project_id' => Project::factory()->create()->id,
            'description' => 'تست توضیحات پیشنهاد یه پروژه توسط فریلنسر'
        ]);

        $user = User::factory()->create(); //another user

        $this->actingAs($user, 'api')
            ->putJson("/api/proposal/update/{$proposal->id}", [
                'description' => 'تست تغییر'
            ])
            ->assertStatus(403);
    }


    public function test_it_fails_validation_if_description_is_invalid()
    {
        $proposal = Proposal::factory()->create([
            'status' => 1,
            'freelancer_id' => User::factory()->freelancer()->create(),
            'project_id' => Project::factory()->create(),
            'description' => 'description test for another proposal'
        ]);

        $user = $proposal->freelancer;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        $this->actingAs($user, 'api')
            ->putJson("/api/proposal/update/{$proposal->id}", [
                'description' => 'a' // کوتاه‌تر از min
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');
    }

    public function test_it_updates_proposal_without_milestones()
    {
        $this->withExceptionHandling();
        Notification::fake();

        $proposal = Proposal::factory()->create([
            'status' => 1,
            'freelancer_id' => User::factory()->freelancer(),
            'project_id' => Project::factory()->create(),
            'description' => 'description test for another proposal'
        ]);

        $user = $proposal->freelancer;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        $response = $this->actingAs($user, 'api')
            ->putJson("/api/proposal/update/{$proposal->id}", [
                'description' => 'توضیحات جدید'
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'پیشنهاد با موفقیت بروزرسانی شد']);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'description' => 'توضیحات جدید'
        ]);

        Notification::assertSentTo($proposal->project->employer, ProposalUpdatedNotification::class);
    }

    public function test_it_updates_proposal_with_milestones_and_calculates_totals()
    {
        Notification::fake();

        $proposal = Proposal::factory()->create([
            'status' => 1,
            'freelancer_id' => User::factory()->freelancer(),
            'project_id' => Project::factory()->create(),
            'description' => 'description test for another proposal'
        ]);

        $user = $proposal->freelancer;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        $milestones = [
            [
                'title' => 'مرحله اول',
                'description' => 'توضیحات مرحله اول',
                'amount' => 1000,
                'duration_time' => 5
            ],
            [
                'title' => 'مرحله دوم',
                'description' => 'توضیحات مرحله دوم',
                'amount' => 2000,
                'duration_time' => 10
            ]
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/proposal/update/{$proposal->id}", [
                'description' => 'توضیحات جدید',
                'milestones' => $milestones
            ]);

        $response->assertOk();

        $this->assertDatabaseCount('proposal_milestones', 2);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'total_amount' => 3000,
        ]);

        Notification::assertSentTo($proposal->project->employer, ProposalUpdatedNotification::class);
    }


    public function test_user_cannot_withdraw_proposal_if_not_authorized()
    {
        $proposal = Proposal::factory()
            ->for(Project::factory(), 'project')
            ->for(User::factory(), 'freelancer')
            ->create([
                'status' => 2, // وضعیت غیرمجاز
                'description' => 'پیشنهاد تستی برای پروژه',
            ]);

        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/proposal/withdraw/{$proposal->id}")
            ->assertStatus(403);
    }


    public function test_freelancer_can_withdraw_own_proposal()
    {
        Notification::fake();

        $freelancer = User::factory()->freelancer()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $freelancer->id,
            'balance' => 0,
        ]);
        $proposal = Proposal::factory()
            ->for(Project::factory(), 'project')
            ->for($freelancer, 'freelancer')
            ->create([
                'status' => 1,
                'description' => 'پیشنهاد تستی برای پروژه',
            ]);

        $this->actingAs($freelancer, 'api')
            ->patchJson("/api/proposal/withdraw/{$proposal->id}")
            ->assertOk()
            ->assertJson(['message' => 'پیشنهاد فریلنسر پس گرفته شده و لغو شد']);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 4,
        ]);

        Notification::assertSentTo($freelancer, WithdrawProposalNotification::class);
    }

    public function test_freelancer_cannot_withdraw_if_proposal_status_is_not_pending()
    {
        $freelancer = User::factory()->freelancer()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $freelancer->id,
            'balance' => 0,
        ]);
        $proposal = Proposal::factory()
            ->for(Project::factory(), 'project')
            ->for($freelancer, 'freelancer')
            ->create([
                'status' => 3, // not pending
                'description' => 'پیشنهاد تستی برای پروژه',
            ]);

        $this->actingAs($freelancer, 'api')
            ->patchJson("/api/proposal/withdraw/{$proposal->id}")
            ->assertStatus(403);
    }

    public function test_other_users_cannot_see_each_other_proposal()
    {
        $freelancerA = User::factory()->freelancer()->create();
        $freelancerB = User::factory()->freelancer()->create();
        $walletA = Wallet::factory()->create([
            'user_id' => $freelancerA->id,
            'balance' => 0,
        ]);
        $walletB = Wallet::factory()->create([
            'user_id' => $freelancerB->id,
            'balance' => 0,
        ]);
        $proposal = Proposal::factory()->create([
            'freelancer_id' => $freelancerA->id,
            'project_id' => Project::factory()->create()->id,
            'description' => 'پیشنهاد تستی برای پروژه',
        ]);
        $response = $this->actingAs($freelancerB, 'api')
            ->getJson("/api/proposal/show/{$proposal->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => false
            ]);
    }

    public function test_authorized_people_can_see_proposal()
    {
        $freelancer = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();
        $admin = User::factory()->admin()->create();
        $freelancerWallet = Wallet::factory()->create([
            'user_id' => $freelancer->id,
            'balance' => 0,
        ]);
        $employerWallet = Wallet::factory()->create([
            'user_id' => $employer->id,
            'balance' => 0,
        ]);
        $adminWallet = Wallet::factory()->create([
            'user_id' => $admin->id,
            'balance' => 0,
        ]);
        $project = Project::factory()->create([
            'user_id' => $employer->id
        ]);
        $proposal = Proposal::factory()->create([
            'freelancer_id' => $freelancer->id,
            'project_id' => $project->id,
            'description' => 'پیشنهاد تستی برای پروژه',
        ]);


        $responseA = $this->actingAs($freelancer, 'api')
            ->getJson("/api/proposal/show/{$proposal->id}");

        $responseA->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $responseB = $this->actingAs($employer, 'api')
            ->getJson("/api/proposal/show/{$proposal->id}");

        $responseB->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $responseC = $this->actingAs($admin, 'api')
            ->getJson("/api/proposal/show/{$proposal->id}");

        $responseC->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);
    }

    public function test_only_authorized_employers_can_thier_project_proposals()
    {
        $employer = User::factory()->employer()->create();
        $employerWallet = Wallet::factory()->create([
            'user_id' => $employer->id,
            'balance' => 0,
        ]);
        $project = Project::factory()->create([
            'user_id' => $employer->id
        ]);
        $freelancerA = User::factory()->freelancer()->create();
        $freelancerB = User::factory()->freelancer()->create();
        $freelancerC = User::factory()->freelancer()->create();

        $freelancerAWallet = Wallet::factory()->create([
            'user_id' => $freelancerA->id,
            'balance' => 0,
        ]);
        $freelancerBWallet = Wallet::factory()->create([
            'user_id' => $freelancerB->id,
            'balance' => 0,
        ]);
        $freelancerCWallet = Wallet::factory()->create([
            'user_id' => $freelancerC->id,
            'balance' => 0,
        ]);
        $proposalA = Proposal::factory()->create([
            'freelancer_id' => $freelancerA->id,
            'project_id' => Project::factory()->create()->id,
            'description' => 'پیشنهاد تستی برای A',
            'total_amount' => 3000
        ]);

        $proposalB = Proposal::factory()->create([
            'freelancer_id' => $freelancerB->id,
            'project_id' => Project::factory()->create()->id,
            'description' => 'پیشنهاد تستی برای B',
            'total_amount' => 5000
        ]);

        $proposalC = Proposal::factory()->create([
            'freelancer_id' => $freelancerC->id,
            'project_id' => Project::factory()->create()->id,
            'description' => 'پیشنهاد تستی برای C',
            'total_amount' => 4000
        ]);

        $this->actingAs($employer, 'api')
            ->getJson("/api/proposal/{$project->id}")
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->actingAs($freelancerA, 'api')
            ->getJson("/api/proposal/{$project->id}")
            ->assertStatus(403)
            ->assertJson(['status' => false]);
    }
}
