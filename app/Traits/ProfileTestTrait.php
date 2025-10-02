<?php

namespace App\Traits;

use App\Models\User\OTP;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

trait ProfileTestTrait
{
    public function user_get_profile($user, $prefixRoute)
    {
        // send request with token
        $response = $this->actingAs($user, 'api')
            ->getJson("$prefixRoute/profile");

        // check response
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        // check user data in response
        $this->assertEquals($user->email, $response->json('data.email'));
        $this->assertEquals($user->name, $response->json('data.name'));
    }

    public function guest_cannot_access_profiles($prefixRoute)
    {
        // send request without login
        $response = $this->getJson("$prefixRoute/profile");

        $response->assertStatus(401);
    }

    public function user_can_update_about_me($user, $prefixRoute)
    {
        $this->actingAs($user, 'api');

        $response = $this->putJson("$prefixRoute/profile/about-me", [
            'about_me' => 'من عاشق برنامه نویسی هستم',
            'gender' => 1
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'اطلاعات شخصی شما با موفقیت بروزرسانی شد',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'about_me' => 'من عاشق برنامه نویسی هستم',
            'gender' => 1
        ]);
    }

    public function user_can_update_profile_with_photo($user, $prefixRoute)
    {
        $this->withExceptionHandling();

        $this->actingAs($user, 'api');

        // فیک کردن دیسک public
        Storage::fake('public');

        // فایل آپلود فیک
        $fakeAvatar = UploadedFile::fake()->image('avatar.jpg');

        // Mock کردن سرویس MediaStorageService
        $this->mock(\App\Http\Services\Public\MediaStorageService::class, function ($mock) use ($fakeAvatar) {
            $mock->shouldReceive('updateImageIfExists')
                ->once()
                ->with(
                    $fakeAvatar,
                    null, // مسیر فعلی عکس کاربر
                    \Mockery::type('string'), // مسیر مقصد
                    null
                )
                ->andReturn('tests/images/fake-avatar.jpg'); // مسیر برگشتی دلخواه
        });

        // درخواست PUT به آپدیت پروفایل
        $response = $this->putJson("$prefixRoute/profile/update", [
            'first_name' => 'آشنا',
            'last_name' => 'آشنایی',
            'national_code' => '2730143815',
            'birth_date' => '1758994476',
            'profile_photo_path' => $fakeAvatar,
        ]);

        // بررسی پاسخ
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'اطلاعات پروفایل با موفقیت بروزرسانی شد',
            ]);

        // چک کردن اینکه مسیر برگشتی در دیتابیس ذخیره شده
        $profilePath = $user->fresh()->profile_photo_path;
        $this->assertEquals('tests/images/fake-avatar.jpg', $profilePath);


        // چک کردن اینکه فایلی روی Storage ساخته نشده (همون Storage::fake)
        // @phpstan-ignore-next-line
        // Storage::disk('public')->assertMissing('tests/images/fake-avatar.jpg');
        $this->assertFalse(Storage::disk('public')->exists('tests/images/fake-avatar.jpg'));

    }

    public function user_can_change_username_if_limit_not_exceeded($user, $prefixRoute)
    {
        $this->actingAs($user);

        $response = $this->patchJson("$prefixRoute/profile/change-username", [
            'username' => 'newUsername',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'نام کاربری شما با موفقیت بروزرسانی شد',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'newUsername',
            'username_change_count' => 1,
        ]);
    }

    public function user_cannot_change_username_more_than_two_times($user, $prefixRoute)
    {
        $user->update(['username_change_count' => 2]);

        $this->actingAs($user);

        $response = $this->patchJson("$prefixRoute/profile/change-username", [
            'username' => 'blockedChange',
        ]);

        $response->assertStatus(403);
    }

    public function user_can_change_password_success($user, $prefixRoute)
    {
        $this->actingAs($user, 'api')
            ->patchJson("$prefixRoute/profile/change-password", [
                'current_password' => 'oldpassword123',
                'new_password' => 'newPassword456!',
                'new_password_confirmation' => 'newPassword456!',
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => null,
                'status' => true,
                'message' => 'کلمه عبور با موفقیت بروزرسانی شد',
            ]);

        $this->assertTrue(Hash::check('newPassword456!', $user->fresh()->password));
    }

    public function fail_change_password_with_wrong_current_password($user, $prefixRoute)
    {
        $this->withExceptionHandling();

        $this->actingAs($user, 'api')
            ->patchJson("$prefixRoute/profile/change-password", [
                'current_password' => 'wrongpassword',
                'new_password' => 'newPassword456!',
                'new_password_confirmation' => 'newPassword456!',
            ])
            ->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'کلمه عبور فعلی نادرست است',
            ]);
    }

    public function user_can_change_mobile_success($user, $prefixRoute)
    {
        $this->actingAs($user, 'api')
            ->patchJson("$prefixRoute/profile/change-mobile", [
                'id' => '09123456789',
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'meta' => ['next_step']
                ]
            ]);
    }

    public function user_cannot_change_mobile_that_already_exists_or_invalid($user, $prefixRoute)
    {
        $this->withExceptionHandling();
        $existingUser = User::factory()->create(['mobile' => '9123456789']);

        $this->actingAs($user, 'api')
            ->patchJson("$prefixRoute/profile/change-mobile", [
                'id' => '09123456789', // شماره تکراری
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'شماره موبایل صحیح نیست یا از قبل وجود دارد'
            ]);

        $this->actingAs($user, 'api')
            ->patchJson("$prefixRoute/profile/change-mobile", [
                'id' => '12345', // شماره نامعتبر
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'شماره موبایل صحیح نیست یا از قبل وجود دارد'
            ]);
    }

    public function confirm_mobile_success($user, $prefixRoute)
    {
        $this->withExceptionHandling();
        $otp = OTP::factory()->create([
            'user_id' => $user->id,
            'otp_code' => '123456',
            'token' => 'test-token-123',
            'login_id' => '9123456789',
            'used' => 0
        ]);

        $this->actingAs($user, 'api')
            ->putJson("$prefixRoute/profile/confirm-mobile/{$otp->token}", [
                'otp' => $otp->otp_code,
            ])
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'شماره موبایل شما با موفقیت تأیید شد',
                'data' => null
            ]);

        $this->assertNotNull($user->fresh()->mobile_verified_at);
        $this->assertEquals($otp->login_id, $user->fresh()->mobile);
    }

    public function fail_confirm_mobile_with_invalid_otp($user, $prefixRoute)
    {
        $otp = OTP::factory()->create([
            'login_id' => '09123456789',
            'user_id' => $user->id,
            'otp_code' => '123456',
            'token' => 'test-token-123',
            'used' => 0
        ]);

        $this->actingAs($user, 'api')
            ->putJson("$prefixRoute/profile/confirm-mobile/{$otp->token}", [
                'otp' => '000000', // wrong otp code
            ])
            ->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'کد وارد شده معتبر نیست'
            ]);

        $this->actingAs($user, 'api')
            ->putJson("$prefixRoute/profile/confirm-mobile/{'test-token-12'}", [ //wrong token
                'otp' => '123456', // right otp code
            ])
            ->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'آدرس وارد شده معتبر نیست'
            ]);
    }
}
