<?php

namespace Tests\Feature;

use App\Models\User\User;
use App\Traits\ProfileTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase, ProfileTestTrait;

    private function authAdmin()
    {
        return User::factory()->admin()->create();
    }

    # [Test]
    public function test_admin_can_get_his_profile()
    {
        $user = $this->authAdmin();
        $this->user_get_profile($user, "/api/admin");
    }

    # [Test]
    public function test_guest_cannot_access_admin_profile()
    {
        $this->guest_cannot_access_profiles('/api/admin');
    }

    # [Test]
    public function test_admin_can_update_about_me()
    {
        $user = $this->authAdmin();
        $this->user_can_update_about_me($user, '/api/admin');
    }

    # [Test]
    public function test_admin_can_update_profile_with_photo()
    {
        $user = $this->authAdmin();
        $this->user_can_update_profile_with_photo($user, "/api/admin");
    }

    # [Test]
    public function test_admin_can_change_username_if_limit_not_exceede()
    {
        $user = $this->authAdmin();
        $this->user_can_change_username_if_limit_not_exceeded($user, '/api/admin');
    }

    # [Test]
    public function test_admin_cannot_change_username_more_than_two_time()
    {
        $user = $this->authAdmin();
        $this->user_cannot_change_username_more_than_two_times($user, "/api/admin");
    }

    # [Test]
    public function test_admin_can_change_password_success()
    {
        $user = User::factory()->admin()->create([
            'password' => Hash::make('oldpassword123')
        ]);
        $this->user_can_change_password_success($user, '/api/admin');
    }

    # [Test]
    public function test_admin_cannot_change_password_with_wrong_current_password()
    {
        $user = User::factory()->admin()->create([
            'password' => Hash::make('oldpassword123')
        ]);
        $this->fail_change_password_with_wrong_current_password($user, '/api/admin');
    }

    # [Test]
    public function test_admin_can_change_mobile_success()
    {
        $user = $this->authAdmin();
        $this->user_can_change_mobile_success($user, '/api/admin');
    }

    # [Test]
    public function test_admin_cannot_change_mobile_that_already_exists_or_invalid()
    {
        $user = $this->authAdmin();
        $this->user_cannot_change_mobile_that_already_exists_or_invalid($user, '/api/admin');
    }

    # [Test]
    public function test_admin_can_confirm_mobile_success()
    {
        $user = $this->authAdmin();
        $this->confirm_mobile_success($user, '/api/admin');
    }

    # [Test]
    public function test_admin_cannot_confirm_mobile_with_invalid_otp()
    {
        $user = $this->authAdmin();
        $this->fail_confirm_mobile_with_invalid_otp($user, '/api/admin');
    }

}
