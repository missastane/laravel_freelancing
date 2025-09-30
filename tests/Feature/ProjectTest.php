<?php

namespace Tests\Feature;

use App\Http\Services\Public\MediaStorageService;
use App\Jobs\SendNotificationForNewProject;
use App\Models\Market\Project;
use App\Models\Market\ProjectCategory;
use App\Models\Market\Skill;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

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


}
