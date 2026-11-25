<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User\AuthSession;
use App\Models\User\User;
use PragmaRX\Google2FA\Google2FA;
class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        // Web response must not expose the refresh token in JSON.
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $response->assertJsonMissingPath('data.refresh_token');

        // Retrieve the created authentication session.
        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertSame('web', $session->client_type);
        $this->assertTrue($session->remember_me);

        // Retrieve both cookies.
        $cookies = collect($response->headers->getCookies());

        $refreshCookie = $cookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $csrfCookie = $cookies->first(
            fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
        );

        $this->assertNotNull($refreshCookie);
        $this->assertNotNull($csrfCookie);

        // Refresh token must be stored in an HttpOnly cookie.
        $this->assertTrue($refreshCookie->isHttpOnly());

        $plainRefreshToken = $refreshCookie->getValue();

        $this->assertNotEmpty($plainRefreshToken);

        // Verify that only the hash is stored in the database.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
        ]);

        $this->assertDatabaseMissing('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => $plainRefreshToken,
        ]);

        // CSRF cookie must be accessible to JavaScript.
        $this->assertFalse($csrfCookie->isHttpOnly());

        $csrfToken = $csrfCookie->getValue();

        $this->assertNotEmpty($csrfToken);

        // Verify that the CSRF token has a valid signature.
        $this->assertTrue(
            app(\App\Support\CsrfToken::class)->isValid($csrfToken)
        );

        // Verify that the access token contains the session ID.
        $accessToken = $response->json('data.access_token');

        $payload = JWTAuth::setToken($accessToken)
            ->getPayload();

        $this->assertEquals($session->id, $payload->get('sid'));
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!123',
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseMissing('auth_sessions', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_mobile_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
            ],
        ]);

        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertSame('mobile', $session->client_type);
        $this->assertTrue($session->remember_me);

        $refreshToken = $response->json('data.refresh_token');

        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $refreshToken),
        ]);
    }

    public function test_web_user_with_two_factor_enabled_receives_challenge_without_tokens(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'requires_two_factor',
                'challenge_token',
                'expires_in',
            ],
        ]);

        $response->assertJsonPath('data.requires_two_factor', true);

        $response->assertJsonMissingPath('data.access_token');
        $response->assertJsonMissingPath('data.refresh_token');

        $this->assertDatabaseMissing('auth_sessions', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_mobile_user_with_two_factor_enabled_receives_challenge_without_tokens(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'requires_two_factor',
                'challenge_token',
                'expires_in',
            ],
        ]);

        $response->assertJsonPath('data.requires_two_factor', true);

        $response->assertJsonMissingPath('data.access_token');
        $response->assertJsonMissingPath('data.refresh_token');

        $this->assertDatabaseMissing('auth_sessions', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_with_enabled_two_factor_authentication_receives_login_challenge(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'status' => true,
            'data' => [
                'requires_two_factor' => true,
                'expires_in' => 300,
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                'requires_two_factor',
                'challenge_token',
                'expires_in',
            ],
        ]);

        $challengeToken = $response->json('data.challenge_token');

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{64}$/',
            $challengeToken
        );

        // No authentication session should be created yet.
        $this->assertDatabaseCount('auth_sessions', 0);

        // No refresh token should be created yet.
        $this->assertDatabaseCount('refresh_tokens', 0);

        // No access token should be returned.
        $response->assertJsonMissingPath('data.access_token');
    }

    public function test_web_user_can_complete_two_factor_login_with_valid_authenticator_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Submit the initial login request.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $loginResponse->assertJsonPath(
            'data.requires_two_factor',
            true
        );

        $loginResponse->assertJsonStructure([
            'data' => [
                'requires_two_factor',
                'challenge_token',
                'expires_in',
            ],
        ]);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // No authentication session or refresh token should exist yet.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);

        // Generate a valid authenticator code.
        $code = $google2fa->getCurrentOtp($secret);

        // Complete the two-factor login.
        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $response->assertStatus(200);

        // Web response must not expose the refresh token in JSON.
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $response->assertJsonMissingPath('data.refresh_token');

        // Retrieve the created authentication session.
        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertSame('web', $session->client_type);
        $this->assertTrue($session->remember_me);

        // Retrieve both cookies.
        $cookies = collect($response->headers->getCookies());

        $refreshCookie = $cookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $csrfCookie = $cookies->first(
            fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
        );

        $this->assertNotNull($refreshCookie);
        $this->assertNotNull($csrfCookie);

        // Refresh token must be stored in an HttpOnly cookie.
        $this->assertTrue($refreshCookie->isHttpOnly());

        $plainRefreshToken = $refreshCookie->getValue();

        $this->assertNotEmpty($plainRefreshToken);

        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
        ]);

        // CSRF cookie must be accessible to JavaScript.
        $this->assertFalse($csrfCookie->isHttpOnly());

        $csrfToken = $csrfCookie->getValue();

        $this->assertNotEmpty($csrfToken);

        $this->assertTrue(
            app(\App\Support\CsrfToken::class)->isValid($csrfToken)
        );

        // Verify that the access token contains the session ID.
        $accessToken = $response->json('data.access_token');

        $payload = JWTAuth::setToken($accessToken)
            ->getPayload();

        $this->assertEquals($session->id, $payload->get('sid'));

        // The authenticator code should have been consumed.
        $this->assertNotNull(
            $user->twoFactorAuth()->firstOrFail()->last_used_at
        );
    }

    public function test_web_user_can_complete_two_factor_login_with_valid_recovery_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $recoveryCode = 'ABC123DEF4';

        $recoveryCodeRecord = $user->twoFactorRecoveryCodes()->create([
            'code_hash' => \Illuminate\Support\Facades\Hash::make($recoveryCode),
        ]);

        // Submit the initial login request.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // No authentication session should exist before 2FA verification.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);

        // Complete login using a recovery code.
        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $recoveryCode,
        ]);

        $response->assertStatus(200);

        // Web response must not expose the refresh token in JSON.
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $response->assertJsonMissingPath('data.refresh_token');

        // Verify that the authentication session was created.
        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertSame('web', $session->client_type);
        $this->assertTrue($session->remember_me);

        // Retrieve the refresh token cookie.
        $cookies = collect($response->headers->getCookies());

        $refreshCookie = $cookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $this->assertNotNull($refreshCookie);
        $this->assertTrue($refreshCookie->isHttpOnly());

        $plainRefreshToken = $refreshCookie->getValue();

        $this->assertNotEmpty($plainRefreshToken);

        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
        ]);

        // The recovery code must be marked as used.
        $this->assertNotNull($recoveryCodeRecord->fresh()->used_at);
    }

    public function test_web_user_cannot_complete_two_factor_login_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Submit the initial login request.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // Attempt to complete login with an invalid code.
        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => '000000',
        ]);

        $response->assertStatus(422);

        $response->assertJsonPath('status', false);

        $response->assertJsonPath(
            'message',
            __('errors.two_factor_code_invalid') . ' ' .
            __('errors.two_factor_attempts_remaining', [
                'remaining' => 4,
            ])
        );

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);

        // The two-factor configuration should remain enabled.
        $this->assertNotNull(
            $user->twoFactorAuth()->firstOrFail()->confirmed_at
        );
    }

    public function test_web_user_cannot_reuse_consumed_two_factor_login_challenge(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Submit the initial login request.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        $code = $google2fa->getCurrentOtp($secret);

        // Complete login successfully.
        $firstResponse = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $firstResponse->assertStatus(200);

        // Try to reuse the same challenge.
        $secondResponse = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $secondResponse->assertStatus(401);

        // Only the first login should have created a session and refresh token.
        $this->assertDatabaseCount('auth_sessions', 1);
        $this->assertDatabaseCount('refresh_tokens', 1);
    }

    public function test_user_cannot_complete_two_factor_login_with_invalid_challenge(): void
    {
        $user = User::factory()->create();

        $user->twoFactorAuth()->create([
            'secret' => app(Google2FA::class)->generateSecretKey(),
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => str_repeat('a', 64),
            'code' => '123456',
        ]);

        $response->assertStatus(401);

        $response->assertJsonPath('status', false);

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_cannot_complete_two_factor_login_with_expired_challenge(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // Move time beyond the five-minute challenge lifetime.
        $this->travel(6)->minutes();

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $response->assertStatus(401);

        $response->assertJsonPath('status', false);

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_cannot_complete_two_factor_login_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // Submit an invalid code five times.
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->postJson('/api/auth/two-factor/verify-login', [
                'challenge_token' => $challengeToken,
                'code' => '000000',
            ]);

            $response->assertStatus(422);
            $response->assertJsonPath('status', false);

            if ($attempt < 5) {
                $response->assertJsonPath(
                    'message',
                    fn(string $message) => str_contains(
                        $message,
                        (string) (5 - $attempt)
                    )
                );
            }
        }

        // The challenge should be deleted after the fifth failed attempt.
        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => '000000',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('status', false);

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_cannot_complete_two_factor_login_with_unverified_email(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge while the email is verified.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // Revoke email verification after the challenge has been created.
        $user->forceFill([
            'email_verified_at' => null,
        ])->save();

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $response->assertStatus(403);

        $response->assertJsonPath('status', false);

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_cannot_complete_two_factor_login_after_disabling_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $twoFactorAuth = $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge while two-factor authentication is enabled.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // Disable two-factor authentication after the challenge is created.
        $twoFactorAuth->update([
            'confirmed_at' => null,
        ]);

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $response->assertStatus(409);

        $response->assertJsonPath('status', false);

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_mobile_user_can_complete_two_factor_login_with_valid_authenticator_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $twoFactorAuth = $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge for a mobile client.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // No session or refresh token should exist before 2FA verification.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $code,
        ]);

        $response->assertStatus(200);

        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.token_type', 'bearer');
        $response->assertJsonPath('data.client_type', null);
        $response->assertJsonPath('data.remember_me', null);

        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
            ],
        ]);

        // Mobile clients receive the refresh token in JSON, not in a cookie.
        $this->assertNotEmpty($response->json('data.refresh_token'));
        $this->assertNull($response->getCookie('refresh_token'));

        $this->assertDatabaseHas('auth_sessions', [
            'user_id' => $user->id,
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $this->assertDatabaseCount('auth_sessions', 1);
        $this->assertDatabaseCount('refresh_tokens', 1);

        // The authenticator code should be marked as used.
        $this->assertNotNull($twoFactorAuth->fresh()->last_used_at);
    }

    public function test_mobile_user_can_complete_two_factor_login_with_valid_recovery_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $recoveryCode = 'ABCDEF1234';

        $twoFactorAuth = $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        $recoveryCodeRecord = $user->twoFactorRecoveryCodes()->create([
            'code_hash' => \Illuminate\Support\Facades\Hash::make($recoveryCode),
            'used_at' => null,
        ]);

        // Create a login challenge for a mobile client.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        // No session or refresh token should exist before 2FA verification.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => $recoveryCode,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);

        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
            ],
        ]);

        $this->assertNotEmpty($response->json('data.refresh_token'));
        $this->assertNull($response->getCookie('refresh_token'));

        $this->assertDatabaseCount('auth_sessions', 1);
        $this->assertDatabaseCount('refresh_tokens', 1);

        $this->assertDatabaseHas('auth_sessions', [
            'user_id' => $user->id,
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $this->assertNotNull($recoveryCodeRecord->fresh()->used_at);

        // The authenticator's last_used_at should remain unchanged.
        $this->assertNull($twoFactorAuth->fresh()->last_used_at);
    }

    public function test_mobile_user_cannot_complete_two_factor_login_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => now(),
        ]);

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->twoFactorAuth()->create([
            'secret' => $secret,
            'confirmed_at' => now(),
            'setup_expires_at' => null,
            'last_used_at' => null,
        ]);

        // Create a login challenge for a mobile client.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $challengeToken = $loginResponse->json('data.challenge_token');

        $response = $this->postJson('/api/auth/two-factor/verify-login', [
            'challenge_token' => $challengeToken,
            'code' => '000000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', false);

        $this->assertStringContainsString(
            '4',
            $response->json('message')
        );

        // No authentication session or refresh token should be created.
        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertDatabaseCount('refresh_tokens', 0);
    }

    public function test_user_without_two_factor_authentication_can_login_directly(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);

        $response->assertJsonPath('status', true);

        // A user without 2FA must not receive a login challenge.
        $response->assertJsonMissingPath('data.requires_two_factor');
        $response->assertJsonMissingPath('data.challenge_token');

        // Authentication should be completed immediately.
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $response->assertJsonMissingPath('data.refresh_token');

        $this->assertDatabaseCount('auth_sessions', 1);
        $this->assertDatabaseCount('refresh_tokens', 1);

        $session = AuthSession::where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $this->assertSame('web', $session->client_type);
    }
}