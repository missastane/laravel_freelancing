<?php

namespace Tests\Feature\Auth;

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_start_two_factor_setup()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'secret',
                    'otpauth_url',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('user_two_factor_auth', [
            'user_id' => $user->id,
            'confirmed_at' => null,
        ]);

        $this->assertNotEmpty($response->json('data.secret'));
        $this->assertStringStartsWith(
            'otpauth://totp/',
            $response->json('data.otpauth_url')
        );
    }

    public function test_guest_cannot_start_two_factor_setup()
    {
        $response = $this->postJson('/api/auth/two-factor/setup');

        $response->assertStatus(401);
    }

    public function test_user_cannot_start_setup_when_two_factor_is_already_enabled()
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'JBSWY3DPEHPK3PXP',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup');

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_already_enabled'),
            ]);

        $this->assertDatabaseCount('user_two_factor_auth', 1);
    }

    public function test_user_can_confirm_two_factor_setup_and_receive_recovery_codes()
    {
        $user = User::factory()->create();

        // Start the two-factor setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        // Retrieve the generated setup secret.
        $twoFactorAuth = $user->twoFactorAuth()->first();

        $this->assertNotNull($twoFactorAuth);

        // Generate a valid TOTP code.
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        // Confirm the setup.
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => $code,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('messages.two_factor_enabled'),
            ])
            ->assertJsonStructure([
                'data' => [
                    'recovery_codes',
                ],
            ]);

        $recoveryCodes = $response->json('data.recovery_codes');

        $this->assertCount(8, $recoveryCodes);

        $this->assertNotNull($twoFactorAuth->fresh()->confirmed_at);
        $this->assertNull($twoFactorAuth->fresh()->setup_expires_at);

        $this->assertDatabaseCount('two_factor_recovery_codes', 8);

        foreach ($recoveryCodes as $recoveryCode) {
            $this->assertDatabaseMissing('two_factor_recovery_codes', [
                'user_id' => $user->id,
                'code_hash' => $recoveryCode,
            ]);
        }
    }

    public function test_user_cannot_confirm_two_factor_setup_with_invalid_code()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => '000000',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_code_invalid'),
            ]);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $this->assertNotNull($twoFactorAuth);
        $this->assertNull($twoFactorAuth->confirmed_at);

        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_user_cannot_confirm_expired_two_factor_setup()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $this->assertNotNull($twoFactorAuth);

        // Move time beyond the 10-minute setup expiration.
        $this->travel(11)->minutes();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => '123456',
            ]);

        $response->assertStatus(410)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_setup_expired'),
            ]);

        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_user_cannot_confirm_two_factor_setup_without_starting_it()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => '123456',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_setup_invalid'),
            ]);

        $this->assertDatabaseCount('user_two_factor_auth', 0);
        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_confirm_two_factor_setup_requires_a_six_digit_code()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $invalidCodes = [
            '12345',
            '1234567',
            'abcdef',
            '12ab56',
        ];

        foreach ($invalidCodes as $code) {
            $response = $this->actingAs($user, 'api')
                ->postJson('/api/auth/two-factor/confirm', [
                    'code' => $code,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('code');
        }

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $this->assertNotNull($twoFactorAuth);
        $this->assertNull($twoFactorAuth->confirmed_at);

        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_user_can_disable_two_factor_with_valid_authenticator_code()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Start two-factor setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $this->assertNotNull($twoFactorAuth);

        // Confirm setup using a valid TOTP code.
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $setupCode = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => $setupCode,
            ])
            ->assertStatus(200);

        $twoFactorAuth->refresh();

        // Mock a newer valid TOTP counter for the disable request.
        $google2faMock = $this->mock(
            \PragmaRX\Google2FA\Google2FA::class,
            function ($mock) {
                $mock->shouldReceive('verifyKeyNewer')
                    ->once()
                    ->andReturn(101);
            }
        );

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/disable', [
                'password' => '123Ss!45678',
                'code' => '654321',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('messages.two_factor_disabled'),
                'data' => null,
            ]);

        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_user_can_disable_two_factor_with_valid_recovery_code()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Start two-factor setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $setupCode = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        // Confirm setup and retrieve recovery codes.
        $confirmResponse = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => $setupCode,
            ]);

        $confirmResponse->assertStatus(200);

        $recoveryCode = $confirmResponse->json('data.recovery_codes.0');

        $this->assertNotEmpty($recoveryCode);

        // Disable using a recovery code instead of a TOTP code.
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/disable', [
                'password' => '123Ss!45678',
                'code' => $recoveryCode,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('messages.two_factor_disabled'),
                'data' => null,
            ]);

        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_user_cannot_disable_two_factor_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Start two-factor setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $setupCode = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        // Confirm setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => $setupCode,
            ])
            ->assertStatus(200);

        // Attempt to disable using an incorrect password.
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/disable', [
                'password' => 'WrongPassword!123',
                'code' => '123456',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_disable_password_invalid'),
            ]);

        // Two-factor authentication must remain enabled.
        $this->assertNotNull(
            $user->twoFactorAuth()->first()?->confirmed_at
        );

        // Recovery codes must remain intact.
        $this->assertDatabaseCount('two_factor_recovery_codes', 8);
    }

    public function test_user_cannot_disable_two_factor_with_invalid_code()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Start two-factor setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/setup')
            ->assertStatus(200);

        $twoFactorAuth = $user->twoFactorAuth()->first();

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $setupCode = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        // Confirm setup.
        $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/confirm', [
                'code' => $setupCode,
            ])
            ->assertStatus(200);

        // Attempt to disable using an invalid code.
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/disable', [
                'password' => '123Ss!45678',
                'code' => 'INVALID123',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_code_invalid'),
            ]);

        // Two-factor authentication must remain enabled.
        $this->assertNotNull(
            $user->twoFactorAuth()->first()?->confirmed_at
        );

        // All recovery codes must remain unused.
        $this->assertDatabaseCount('two_factor_recovery_codes', 8);

        $this->assertDatabaseMissing('two_factor_recovery_codes', [
            'user_id' => $user->id,
            'used_at' => now(),
        ]);
    }

    public function test_user_cannot_disable_two_factor_when_it_is_not_enabled()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/two-factor/disable', [
                'password' => '123Ss!45678',
                'code' => '123456',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => __('errors.two_factor_not_enabled'),
            ]);

        $this->assertDatabaseCount('user_two_factor_auth', 0);
        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }

    public function test_disable_two_factor_requires_password_and_code()
    {
        $user = User::factory()->create();

        $invalidPayloads = [
            [],
            ['password' => '123Ss!45678'],
            ['code' => '123456'],
            ['password' => '', 'code' => ''],
        ];

        foreach ($invalidPayloads as $payload) {
            $response = $this->actingAs($user, 'api')
                ->postJson('/api/auth/two-factor/disable', $payload);

            $response->assertStatus(422);

            if (!isset($payload['password']) || $payload['password'] === '') {
                $response->assertJsonValidationErrors('password');
            }

            if (!isset($payload['code']) || $payload['code'] === '') {
                $response->assertJsonValidationErrors('code');
            }
        }

        $this->assertDatabaseCount('user_two_factor_auth', 0);
        $this->assertDatabaseCount('two_factor_recovery_codes', 0);
    }
}