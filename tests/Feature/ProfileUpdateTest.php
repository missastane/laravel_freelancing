<?php

namespace Tests\Feature;

use App\Exceptions\User\WrongCurrentPasswordException;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Market\Project;
use App\Models\Market\ProjectCategory;
use App\Models\Market\Skill;
use App\Models\User\OTP;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

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






    // public function test_direct_throw_wrong_password_exception()
    // {
    //     $this->withExceptionHandling();

    //     Route::get('/_test-exception', function () {
    //         throw new WrongCurrentPasswordException();
    //     });

    //     $response = $this->get('/_test-exception');

    //     $response->assertStatus(403)
    //         ->assertJson([
    //             'status' => false,
    //             'message' => 'کلمه عبور فعلی نادرست است',
    //         ]);
    // }


}
