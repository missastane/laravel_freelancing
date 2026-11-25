<?php

namespace Tests\Unit;

use App\Http\Services\Auth\TwoFactorService;
use App\Models\User\TwoFactorRecoveryCode;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_two_factor_setup_successfully(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        $this->travelTo(now());

        $result = $service->startSetup($user);

        $twoFactorAuth = $user->twoFactorAuth()->firstOrFail();

        // The setup should be successful.
        $this->assertTrue($result['status']);
        $this->assertSame(200, $result['code']);

        // A secret should be generated and stored.
        $this->assertNotEmpty($result['data']['secret']);
        $this->assertNotEmpty($twoFactorAuth->secret);

        // The secret returned to the user should match the stored secret.
        $this->assertSame(
            $result['data']['secret'],
            $twoFactorAuth->secret
        );

        // The setup should remain unconfirmed.
        $this->assertNull($twoFactorAuth->confirmed_at);

        // The setup should expire after 10 minutes.
        $this->assertEquals(
            now()->addMinutes(10)->timestamp,
            $twoFactorAuth->setup_expires_at->timestamp
        );

        // The QR authentication URL should be generated.
        $this->assertStringStartsWith(
            'otpauth://',
            $result['data']['otpauth_url']
        );

        // No recovery codes should exist before confirmation.
        $this->assertSame(0, TwoFactorRecoveryCode::count());
    }

    public function test_it_rejects_setup_when_two_factor_is_already_enabled(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'existing-secret',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
        ]);

        $service = app(TwoFactorService::class);

        $result = $service->startSetup($user);

        // The setup should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        // The existing two-factor configuration should remain unchanged.
        $this->assertNotNull(
            $user->twoFactorAuth()->firstOrFail()->confirmed_at
        );

        $this->assertSame(0, TwoFactorRecoveryCode::count());
    }

    public function test_it_rejects_confirmation_when_setup_does_not_exist(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        $result = $service->confirmSetup($user, '123456');

        // Confirmation should fail when no setup exists.
        $this->assertFalse($result['status']);
        $this->assertSame(400, $result['code']);
    }

    public function test_it_rejects_confirmation_when_two_factor_is_already_enabled(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'existing-secret',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
        ]);

        $service = app(TwoFactorService::class);

        $result = $service->confirmSetup($user, '123456');

        // An already enabled configuration cannot be confirmed again.
        $this->assertFalse($result['status']);
        $this->assertSame(400, $result['code']);

        // No additional recovery codes should be created.
        $this->assertSame(0, TwoFactorRecoveryCode::count());
    }

    public function test_it_deletes_expired_setup_and_rejects_confirmation(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        $service->startSetup($user);

        $twoFactorAuth = $user->twoFactorAuth()->firstOrFail();

        // Simulate an expired setup.
        $twoFactorAuth->update([
            'setup_expires_at' => now()->subMinute(),
        ]);

        $result = $service->confirmSetup($user, '123456');

        // The expired setup should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(410, $result['code']);

        // The expired setup record should be deleted.
        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // No recovery codes should be created.
        $this->assertSame(0, TwoFactorRecoveryCode::count());
    }

    public function test_it_rejects_an_invalid_authenticator_code(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'test-secret',
            'confirmed_at' => null,
            'setup_expires_at' => now()->addMinutes(10),
        ]);

        // Mock Google2FA to return an invalid verification result.
        $google2fa = $this->mock(
            Google2FA::class,
            function ($mock) {
                $mock->shouldReceive('verifyKeyNewer')
                    ->once()
                    ->andReturnFalse();
            }
        );

        $service = new TwoFactorService($google2fa);

        $result = $service->confirmSetup($user, '123456');

        // Invalid codes should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(422, $result['code']);

        // The setup should remain available for another attempt.
        $this->assertNotNull(
            $user->twoFactorAuth()->first()
        );

        // Two-factor authentication should remain disabled.
        $this->assertNull(
            $user->twoFactorAuth()->first()->confirmed_at
        );

        // No recovery codes should be generated.
        $this->assertSame(0, TwoFactorRecoveryCode::count());
    }

    public function test_it_confirms_two_factor_setup_and_generates_recovery_codes(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        $setupResult = $service->startSetup($user);

        $twoFactorAuth = $user->twoFactorAuth()->firstOrFail();

        $google2fa = app(Google2FA::class);

        // Generate a real TOTP code using the stored secret.
        $code = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        $result = $service->confirmSetup($user, $code);

        $twoFactorAuth->refresh();

        // The confirmation should succeed.
        $this->assertTrue($result['status']);
        $this->assertSame(200, $result['code']);

        // Two-factor authentication should now be enabled.
        $this->assertNotNull($twoFactorAuth->confirmed_at);

        // The setup expiration should be cleared.
        $this->assertNull($twoFactorAuth->setup_expires_at);

        // The last accepted TOTP timestamp should be stored.
        $this->assertNotNull($twoFactorAuth->last_used_at);

        // Exactly 8 recovery codes should be returned.
        $recoveryCodes = $result['data']['recovery_codes'];

        $this->assertCount(8, $recoveryCodes);

        // Each recovery code should contain 10 hexadecimal characters.
        foreach ($recoveryCodes as $recoveryCode) {
            $this->assertMatchesRegularExpression(
                '/^[A-F0-9]{10}$/',
                $recoveryCode
            );
        }

        // Exactly 8 recovery code records should be stored.
        $this->assertSame(8, TwoFactorRecoveryCode::count());

        // Each recovery code should be stored as a hash.
        $storedCodes = TwoFactorRecoveryCode::all();

        foreach ($storedCodes as $storedCode) {
            $this->assertNotEmpty($storedCode->code_hash);

            $this->assertTrue(
                collect($recoveryCodes)->contains(
                    fn($recoveryCode) => \Illuminate\Support\Facades\Hash::check(
                        $recoveryCode,
                        $storedCode->code_hash
                    )
                )
            );
        }

        // The original recovery codes should not be stored as plain text.
        foreach ($recoveryCodes as $recoveryCode) {
            $this->assertDatabaseMissing('two_factor_recovery_codes', [
                'code_hash' => $recoveryCode,
            ]);
        }
    }

    public function test_it_rejects_confirmation_after_successful_setup(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        $service->startSetup($user);

        $twoFactorAuth = $user->twoFactorAuth()->firstOrFail();

        $google2fa = app(Google2FA::class);

        $code = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        // Confirm the setup successfully.
        $firstResult = $service->confirmSetup($user, $code);

        $this->assertTrue($firstResult['status']);

        // Attempt to confirm the same setup again.
        $secondResult = $service->confirmSetup($user, $code);

        // The second confirmation should be rejected.
        $this->assertFalse($secondResult['status']);
        $this->assertSame(400, $secondResult['code']);

        // No additional recovery codes should be created.
        $this->assertSame(8, TwoFactorRecoveryCode::count());
    }

    public function test_it_disables_two_factor_authentication_with_valid_authenticator_code(): void
    {
        $user = User::factory()->create();

        // Create an enabled two-factor configuration.
        $user->twoFactorAuth()->create([
            'secret' => 'test-secret',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => 100,
        ]);

        // Mock Google2FA to accept a newer TOTP time counter.
        $google2fa = $this->mock(
            Google2FA::class,
            function ($mock) {
                $mock->shouldReceive('verifyKeyNewer')
                    ->once()
                    ->with('test-secret', '123456', 100)
                    ->andReturn(101);
            }
        );

        $service = new TwoFactorService($google2fa);

        $result = $service->disable(
            $user,
            'password',
            '123456'
        );

        // The operation should succeed.
        $this->assertTrue($result['status']);
        $this->assertSame(200, $result['code']);

        // The two-factor configuration should be deleted.
        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // All recovery codes should be deleted.
        $this->assertSame(
            0,
            TwoFactorRecoveryCode::where('user_id', $user->id)->count()
        );
    }

    public function test_it_disables_two_factor_authentication_with_valid_recovery_code(): void
    {
        $user = User::factory()->create();

        $service = app(TwoFactorService::class);

        // Start and confirm two-factor setup.
        $service->startSetup($user);

        $twoFactorAuth = $user->twoFactorAuth()->firstOrFail();

        $google2fa = app(Google2FA::class);

        $setupCode = $google2fa->getCurrentOtp($twoFactorAuth->secret);

        $setupResult = $service->confirmSetup($user, $setupCode);

        $this->assertTrue($setupResult['status']);

        // Retrieve one of the generated recovery codes.
        $recoveryCodes = $setupResult['data']['recovery_codes'];

        $recoveryCode = $recoveryCodes[0];

        $result = $service->disable(
            $user,
            'password',
            $recoveryCode
        );

        // The operation should succeed.
        $this->assertTrue($result['status']);
        $this->assertSame(200, $result['code']);

        // The two-factor configuration should be deleted.
        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // All recovery codes should be deleted.
        $this->assertSame(
            0,
            TwoFactorRecoveryCode::where('user_id', $user->id)->count()
        );
    }

    public function test_it_rejects_disabling_two_factor_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'test-secret',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => 100,
        ]);

        $google2fa = $this->mock(
            Google2FA::class,
            function ($mock) {
                // The authenticator code should never be checked.
                $mock->shouldNotReceive('verifyKeyNewer');
            }
        );

        $service = new TwoFactorService($google2fa);

        $result = $service->disable(
            $user,
            'wrong-password',
            '123456'
        );

        // The operation should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(422, $result['code']);

        // The two-factor configuration should remain enabled.
        $this->assertDatabaseHas('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // Recovery codes should remain untouched.
        $this->assertSame(
            0,
            TwoFactorRecoveryCode::where('user_id', $user->id)->count()
        );
    }

    public function test_it_rejects_disabling_two_factor_when_not_enabled(): void
    {
        $user = User::factory()->create();

        $google2fa = $this->mock(
            Google2FA::class,
            function ($mock) {
                // The authenticator code should never be checked.
                $mock->shouldNotReceive('verifyKeyNewer');
            }
        );

        $service = new TwoFactorService($google2fa);

        $result = $service->disable(
            $user,
            'password',
            '123456'
        );

        // The operation should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(409, $result['code']);

        // No two-factor configuration should exist.
        $this->assertDatabaseMissing('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // No recovery codes should exist.
        $this->assertSame(
            0,
            TwoFactorRecoveryCode::where('user_id', $user->id)->count()
        );
    }

    public function test_it_rejects_disabling_two_factor_with_invalid_code(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => 'test-secret',
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => 100,
        ]);

        // Create an unused recovery code with a different value.
        $user->twoFactorRecoveryCodes()->create([
            'code_hash' => \Illuminate\Support\Facades\Hash::make('VALIDCODE1'),
        ]);

        $google2fa = $this->mock(
            Google2FA::class,
            function ($mock) {
                $mock->shouldReceive('verifyKeyNewer')
                    ->once()
                    ->with('test-secret', '123456', 100)
                    ->andReturnFalse();
            }
        );

        $service = new TwoFactorService($google2fa);

        $result = $service->disable(
            $user,
            'password',
            '123456'
        );

        // The operation should be rejected.
        $this->assertFalse($result['status']);
        $this->assertSame(422, $result['code']);

        // The two-factor configuration should remain enabled.
        $this->assertDatabaseHas('user_two_factor_auth', [
            'user_id' => $user->id,
        ]);

        // The recovery code should remain unused.
        $this->assertDatabaseHas('two_factor_recovery_codes', [
            'user_id' => $user->id,
            'used_at' => null,
        ]);
    }
}
