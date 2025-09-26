<?php

namespace Tests\Feature;

use App\Models\User\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'لینک بازیابی کلمه عبور به ایمیل شما ارسال شد',
                'data' => null
            ]);

        // check sending notification
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // ساخت توکن ریست (Fake کردن Notification حذف شد)
        $token = Password::createToken($user);

        $newPassword = 'r@mZ4Ob00r';
        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'رمز عبور شما با موفقیت تغییر یافت',
            ]);

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

}
