<?php

namespace Tests\Feature;

use App\Models\User\OTP;
use App\Models\User\User;
use App\Traits\ProfileTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase, ProfileTestTrait;

    private function authUser()
    {
        return User::factory()->create();
    }

    # [Test]
    public function test_user_can_get_his_profile()
    {
        $user = $this->authUser();
        $this->user_get_profile($user, "/api");
    }

    # [Test]
    public function test_guest_cannot_access_user_profile()
    {
        $this->guest_cannot_access_profiles('/api');
    }

    # [Test]
    public function test_user_can_update_about_me()
    {
        $user = $this->authUser();
        $this->user_can_update_about_me($user, '/api');
    }

    # [Test]
    public function test_user_can_update_profile_with_photo()
    {
        $user = $this->authUser();
        $this->user_can_update_profile_with_photo($user, "/api");
    }

    # [Test]
    public function test_user_can_change_username_if_limit_not_exceede()
    {
        $user = $this->authUser();
        $this->user_can_change_username_if_limit_not_exceeded($user, '/api');
    }

    # [Test]
    public function test_user_cannot_change_username_more_than_two_time()
    {
        $user = $this->authUser();
        $this->user_cannot_change_username_more_than_two_times($user, "/api");
    }

    # [Test]
    public function test_user_can_change_password_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);
        $this->user_can_change_password_success($user, '/api');
    }

    # [Test]
    public function test_user_cannot_change_password_with_wrong_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);
        $this->fail_change_password_with_wrong_current_password($user, '/api');
    }

    # [Test]
    public function test_user_can_change_mobile_success()
    {
        $user = $this->authUser();
        $this->user_can_change_mobile_success($user, '/api');
    }

    # [Test]
    public function test_user_cannot_change_mobile_that_already_exists_or_invalidd()
    {
        $user = $this->authUser();
        $this->user_cannot_change_mobile_that_already_exists_or_invalid($user, '/api');
    }

    # [Test]
    public function test_user_can_confirm_mobile_success()
    {
        $user = $this->authUser();
        $this->confirm_mobile_success($user, '/api');
    }

    # [Test]
    public function test_user_cannot_confirm_mobile_with_invalid_otp()
    {
        $user = $this->authUser();
        $this->fail_confirm_mobile_with_invalid_otp($user,'/api');
    }
}