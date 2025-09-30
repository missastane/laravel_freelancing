<?php

namespace Tests\Feature;

use App\Http\Services\Notification\SubscriptionUsageManagerService;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Payment\Wallet;
use App\Models\User\User;
use App\Notifications\AddNewProposal;
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

}
