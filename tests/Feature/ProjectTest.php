<?php

namespace Tests\Feature;

use App\Http\Services\Public\MediaStorageService;
use App\Jobs\SendNotificationForNewProject;
use App\Models\Market\Project;
use App\Models\Market\ProjectCategory;
use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\Market\Skill;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_freelancer_can_only_see_pending_projects()
    {
        $freelancer = User::factory()->freelancer()->create();

        // make some projects with different statuses 
        Project::factory()->count(5)->create(['status' => 1]);
        Project::factory()->count(5)->create(['status' => 2]);
        Project::factory()->count(5)->create(['status' => 3]);
        Project::factory()->count(5)->create(['status' => 4]);

        $response = $this->actingAs($freelancer, 'api')->getJson('/api/project');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => null,
            ]);

        $response->assertJsonCount(5, 'data.data');
        $this->assertEquals('در حال بررسی توسط فریلنسرها', $response->json('data.data.0.status'));
    }

    public function test_store_project_success()
    {
        $this->withExceptionHandling();

        $user = User::factory()->employer()->create();

        $category = ProjectCategory::factory()->create();

        $skills = Skill::factory()->count(2)->create();

        $data = [
            'title' => 'پروژه تستی',
            'description' => 'توضیحات تست پروژه',
            'project_category_id' => $category->id,
            'duration_time' => 7,
            'amount' => 1500000,
            'skills' => $skills->pluck('id')->toArray(),
            'files' => [
                UploadedFile::fake()->create('file1.pdf', 100),
                UploadedFile::fake()->create('file2.jpg', 200),
            ],
        ];

        $this->mock(MediaStorageService::class, function ($mock) {
            $mock->shouldReceive('storeMultipleFiles')->andReturn([
                'tests/images/fake-file1.jpg',
                'tests/images/fake-file2.pdf',
            ]);
        });

        $this->mock(SendNotificationForNewProject::class, function ($mock) {
            $mock->shouldReceive('dispatch')->andReturnTrue();
        });

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/project/store', $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'پروژه با موفقیت ثبت شد'
            ]);

        $this->assertDatabaseHas('projects', [
            'title' => $data['title'],
            'description' => $data['description'],
            'user_id' => $user->id,
            'project_category_id' => $category->id,
            'duration_time' => $data['duration_time'],
            'amount' => $data['amount'],
        ]);

        $project = Project::where('title', $data['title'])->first();
        $this->assertCount(2, $project->skills->toArray());
    }


    public function test_employer_can_only_see_their_own_projects()
    {
        $employerA = User::factory()->employer()->create();
        $employerB = User::factory()->employer()->create();

        Project::factory()->count(2)->create(['user_id' => $employerA->id]);
        Project::factory()->count(3)->create(['user_id' => $employerB->id]);

        $response = $this->actingAs($employerA, 'api')->getJson('/api/project/user-projects');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => null,
            ]);

        $projects = $response->json('data.data');

        $this->assertCount(2, $projects);
        foreach ($projects as $project) {
            $this->assertEquals($employerA->id, $project['employer']['id']);
        }
    }

    public function test_employer_with_no_projects_gets_empty_list()
    {
        $employer = User::factory()->employer()->create();

        $response = $this->actingAs($employer, 'api')->getJson('/api/project/user-projects');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => null,
            ])
            ->assertJsonCount(0, 'data.data'); // not exist any project
    }

    public function test_employer_can_update_own_pending_project()
    {
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create([
            'user_id' => $employer->id,
            'status' => 1, // pending
        ]);

        $payload = [
            'title' => 'پروژه ویرایش شده',
            'description' => 'توضیحات جدید',
            'project_category_id' => ProjectCategory::factory()->create()->id,
            'duration_time' => 10,
            'amount' => 2000000,
            'files' => [], // اگه فایل الزامی نیست
            'skills' => [Skill::factory()->create()->id],
        ];

        $this->mock(MediaStorageService::class, function ($mock) {
            $mock->shouldReceive('storeMultipleFiles')->andReturn([]);
        });

        $response = $this->actingAs($employer, 'api')
            ->putJson("/api/project/update/{$project->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'پروژه با موفقیت بروزرسانی شد',
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'پروژه ویرایش شده',
        ]);
    }

    public function test_update_project_validation_error()
    {
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create([
            'user_id' => $employer->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($employer, 'api')
            ->putJson("/api/project/update/{$project->id}", [
                'title' => '', // نامعتبر
            ]);

        $response->assertStatus(422);
    }

    public function test_employer_cannot_update_other_users_project()
    {
        $this->withExceptionHandling();
        $employerA = User::factory()->employer()->create();
        $employerB = User::factory()->employer()->create();

        $project = Project::factory()->create([
            'user_id' => $employerB->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($employerA, 'api')
            ->putJson("/api/project/update/{$project->id}", [
                'title' => 'عنوان جدید',
                'description' => 'توضیحات جدید',
                'project_category_id' => ProjectCategory::factory()->create()->id,
                'duration_time' => 5,
                'amount' => 1000000,
                'skills' => [Skill::factory()->create()->id],
                'files' => [],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'شما مجاز به انجام این عملیات نیستید',
            ]);
    }

    public function test_employer_can_delete_own_project()
    {
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($employer, 'api')
            ->deleteJson("/api/project/delete/{$project->id}");

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'پروژه با موفقیت حذف شد',
            ]);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }


    public function test_admin_can_delete_any_project()
    {
        $admin = User::factory()->admin()->create();
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($admin, 'api')
            ->deleteJson("/api/admin/market/project/delete/{$project->id}");

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'پروژه با موفقیت حذف شد',
            ]);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_delete_other_users_project()
    {
        $employerA = User::factory()->employer()->create();
        $employerB = User::factory()->employer()->create();
        $project = Project::factory()->create(['user_id' => $employerB->id]);

        $response = $this->actingAs($employerA, 'api')
            ->deleteJson("/api/project/delete/{$project->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'شما مجاز به انجام این عملیات نیستید',
            ]);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }


    public function test_employer_can_view_project_details()
    {
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create(['user_id' => $employer->id]);

        $proposalA = Proposal::factory()->create([
            'project_id' => $project->id,
            'description' => 'این پیشنهاد آخر منه',
            'total_amount' => 200000,
            'freelancer_id' => User::factory()->freelancer()->create()->id
        ]);
        $milestoneA = ProposalMilestone::factory()->create([
            'proposal_id' => $proposalA->id,
            'title' => 'مرحله اول پیشنهاد',
            'description' => 'توضیح مرحله اول پیشنهاد',
            'amount' => 200000,
            'duration_time' => 2
        ]);
        $proposalB = Proposal::factory()->create([
            'project_id' => $project->id,
            'description' => 'این پیشنهاد آخر منه',
            'total_amount' => 300000,
            'freelancer_id' => User::factory()->freelancer()->create()->id
        ]);
        $milestoneB = ProposalMilestone::factory()->create([
            'proposal_id' => $proposalB->id,
            'title' => 'مرحله اول پیشنهاد',
            'description' => 'توضیح مرحله اول پیشنهاد',
            'amount' => 300000,
            'duration_time' => 3
        ]);

        $response = $this->actingAs($employer, 'api')
            ->getJson("/api/project/details/{$project->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'project' => ['id', 'title'],
                    'stats' => ['min_days', 'max_days', 'min_price', 'max_price'],
                ]
            ]);
    }

    public function test_non_employer_cannot_view_project_details_without_plan()
    {
        $freelancer = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();
        $project = Project::factory()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($freelancer, 'api')
            ->getJson("/api/project/details/{$project->id}");

        $response->assertStatus(429);
    }

}
