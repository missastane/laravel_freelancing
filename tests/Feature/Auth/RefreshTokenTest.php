<?php

namespace Tests\Feature\Auth;

use App\Http\Services\Auth\RefreshTokenService;
use App\Models\User\AuthSession;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

     public function test_web_user_can_refresh_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a web authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        // Retrieve login cookies.
        $loginCookies = collect($loginResponse->headers->getCookies());

        $refreshCookie = $loginCookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $csrfCookie = $loginCookies->first(
            fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
        );

        $this->assertNotNull($refreshCookie);
        $this->assertNotNull($csrfCookie);

        $refreshToken = $refreshCookie->getValue();
        $csrfToken = $csrfCookie->getValue();

        $this->assertNotEmpty($refreshToken);
        $this->assertNotEmpty($csrfToken);

        // Verify that the CSRF token is valid.
        $this->assertTrue(
            app(\App\Support\CsrfToken::class)->isValid($csrfToken)
        );

        // Retrieve the authentication session.
        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);

        // Refresh using the HttpOnly refresh cookie and valid CSRF token.
        $response = $this->withUnencryptedCookie(
            'refresh_token',
            $refreshToken
        )->call(
                'POST',
                '/api/auth/refresh',
                [],
                [
                    'refresh_token' => $refreshToken,
                    'XSRF-TOKEN' => $csrfToken,
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ORIGIN' => 'http://localhost:5173',
                    'HTTP_X_CSRF_TOKEN' => $csrfToken,
                ]
            );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        // Web must not receive the refresh token in JSON.
        $response->assertJsonMissingPath('data.refresh_token');

        // A new refresh token must be issued through the cookie.
        $response->assertCookie('refresh_token');

        // The CSRF cookie must remain unchanged after refresh.
        // The CSRF cookie must not be rotated or reissued during refresh.
        $refreshedCookies = collect($response->headers->getCookies());

        $this->assertNull(
            $refreshedCookies->first(
                fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
            )
        );

        // The original refresh token must have been consumed.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $refreshToken),
        ]);

        $oldToken = \App\Models\User\RefreshToken::where(
            'token_hash',
            hash('sha256', $refreshToken)
        )->first();

        $this->assertNotNull($oldToken);
        $this->assertNotNull($oldToken->used_at);
        $this->assertNotNull($oldToken->revoked_at);

        // The new refresh token must be different from the old one.
        $newRefreshCookie = collect($response->headers->getCookies())
            ->first(fn($cookie) => $cookie->getName() === 'refresh_token');

        $this->assertNotNull($newRefreshCookie);
        $this->assertNotEquals(
            $refreshToken,
            $newRefreshCookie->getValue()
        );

        // The new access token must belong to the same session.
        $newAccessToken = $response->json('data.access_token');

        $payload = JWTAuth::setToken($newAccessToken)->getPayload();

        $this->assertEquals($session->id, $payload->get('sid'));
    }

    public function test_web_refresh_requires_csrf_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $refreshCookie = collect($loginResponse->headers->getCookies())
            ->first(fn($cookie) => $cookie->getName() === 'refresh_token');

        $this->assertNotNull($refreshCookie);

        $refreshToken = $refreshCookie->getValue();

        // Send refresh request without CSRF cookie and header.
        $response = $this->withUnencryptedCookie(
            'refresh_token',
            $refreshToken
        )->call(
                'POST',
                '/api/auth/refresh',
                [],
                [
                    'refresh_token' => $refreshToken,
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ORIGIN' => 'http://localhost:5173',
                ]
            );

        $response->assertStatus(419)
            ->assertJson([
                'status' => false,
                'message' => 'CSRF token mismatch.',
            ]);
    }

    public function test_web_refresh_rejects_invalid_csrf_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a web authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        // Retrieve the refresh cookie.
        $refreshCookie = collect($loginResponse->headers->getCookies())
            ->first(fn($cookie) => $cookie->getName() === 'refresh_token');

        $this->assertNotNull($refreshCookie);

        $refreshToken = $refreshCookie->getValue();

        $this->assertNotEmpty($refreshToken);

        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);

        // Use an invalid CSRF token in both cookie and header.
        $invalidCsrfToken = 'invalid-csrf-token';

        $response = $this->withUnencryptedCookie(
            'refresh_token',
            $refreshToken
        )->call(
                'POST',
                '/api/auth/refresh',
                [],
                [
                    'refresh_token' => $refreshToken,
                    'XSRF-TOKEN' => $invalidCsrfToken,
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ORIGIN' => 'http://localhost:5173',
                    'HTTP_X_CSRF_TOKEN' => $invalidCsrfToken,
                ]
            );

        // The request must be rejected because the CSRF token is invalid.
        $response->assertStatus(419)
            ->assertJson([
                'status' => false,
                'message' => 'CSRF token mismatch.',
            ]);

        // The original refresh token must remain unused.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $refreshToken),
            'used_at' => null,
            'revoked_at' => null,
        ]);

        // The authentication session must remain active.
        $this->assertNull($session->fresh()->revoked_at);
    }

    public function test_web_refresh_rejects_mismatched_csrf_tokens()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a web authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        // Retrieve login cookies.
        $loginCookies = collect($loginResponse->headers->getCookies());

        $refreshCookie = $loginCookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $csrfCookie = $loginCookies->first(
            fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
        );

        $this->assertNotNull($refreshCookie);
        $this->assertNotNull($csrfCookie);

        $refreshToken = $refreshCookie->getValue();
        $csrfCookieToken = $csrfCookie->getValue();

        $this->assertNotEmpty($refreshToken);
        $this->assertNotEmpty($csrfCookieToken);

        // Generate another valid CSRF token for the header.
        $csrfHeaderToken = app(\App\Support\CsrfToken::class)->generate();

        $this->assertNotEquals(
            $csrfCookieToken,
            $csrfHeaderToken
        );

        // Verify that both tokens are individually valid.
        $csrfService = app(\App\Support\CsrfToken::class);

        $this->assertTrue($csrfService->isValid($csrfCookieToken));
        $this->assertTrue($csrfService->isValid($csrfHeaderToken));

        // Retrieve the authentication session.
        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);

        // Send different CSRF tokens in cookie and header.
        $response = $this->withUnencryptedCookie(
            'refresh_token',
            $refreshToken
        )->call(
                'POST',
                '/api/auth/refresh',
                [],
                [
                    'refresh_token' => $refreshToken,
                    'XSRF-TOKEN' => $csrfCookieToken,
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ORIGIN' => 'http://localhost:5173',
                    'HTTP_X_CSRF_TOKEN' => $csrfHeaderToken,
                ]
            );

        // The request must be rejected because the CSRF tokens do not match.
        $response->assertStatus(419)
            ->assertJson([
                'status' => false,
                'message' => 'CSRF token mismatch.',
            ]);

        // The original refresh token must remain unused.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $refreshToken),
            'used_at' => null,
            'revoked_at' => null,
        ]);

        // The authentication session must remain active.
        $this->assertNull($session->fresh()->revoked_at);
    }

    public function test_web_refresh_rejects_unverified_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
            'email_verified_at' => null,
        ]);

        // Create a web authentication session directly.
        $refreshTokenService = app(RefreshTokenService::class);

        $sessionData = $refreshTokenService->createSession(
            $user,
            'web',
            true
        );

        $session = $sessionData['session'];
        $refreshToken = $sessionData['refresh_token'];

        // Generate a valid CSRF token.
        $csrfToken = app(\App\Support\CsrfToken::class)->generate();

        $response = $this->withUnencryptedCookie(
            'refresh_token',
            $refreshToken
        )->call(
                'POST',
                '/api/auth/refresh',
                [],
                [
                    'refresh_token' => $refreshToken,
                    'XSRF-TOKEN' => $csrfToken,
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ORIGIN' => 'http://localhost:5173',
                    'HTTP_X_CSRF_TOKEN' => $csrfToken,
                ]
            );

        // The refresh request must be rejected.
        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => __('errors.email_has_not_verified'),
            ]);

        // No access token or replacement refresh token must be issued.
        $response->assertJsonMissingPath('data.access_token');
        $response->assertJsonMissingPath('data.refresh_token');

        // The original refresh token must remain unused but revoked.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $refreshToken),
            'used_at' => null,
            'revoked_at' => $session->fresh()->revoked_at,
        ]);

        // The authentication session must be revoked.
        $this->assertNotNull($session->fresh()->revoked_at);

        // All active refresh tokens belonging to this session must be revoked.
        $this->assertDatabaseMissing('refresh_tokens', [
            'session_id' => $session->id,
            'revoked_at' => null,
        ]);
    }

    public function test_mobile_user_can_refresh_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a mobile authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'refresh_token',
                ],
            ]);

        $refreshToken = $loginResponse->json('data.refresh_token');

        $this->assertNotEmpty($refreshToken);

        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertEquals('mobile', $session->client_type);

        // Refresh using the Bearer token.
        $response = $this->withToken($refreshToken)
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'refresh_token',
                ],
            ]);

        // Mobile must receive the rotated refresh token in JSON.
        $newRefreshToken = $response->json('data.refresh_token');

        $this->assertNotEmpty($newRefreshToken);
        $this->assertNotEquals($refreshToken, $newRefreshToken);

        // The old refresh token must have been consumed.
        $oldToken = \App\Models\User\RefreshToken::where(
            'token_hash',
            hash('sha256', $refreshToken)
        )->first();

        $this->assertNotNull($oldToken);
        $this->assertNotNull($oldToken->used_at);
        $this->assertNotNull($oldToken->revoked_at);

        // The new refresh token must be stored as a hash.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $newRefreshToken),
        ]);

        // Mobile must not receive a refresh token cookie.
        $response->assertCookieMissing('refresh_token');

        // The new access token must belong to the same session.
        $newAccessToken = $response->json('data.access_token');

        $payload = JWTAuth::setToken($newAccessToken)->getPayload();

        $this->assertEquals($session->id, $payload->get('sid'));
    }

    public function test_reusing_old_refresh_token_revokes_session()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a mobile authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
            'remember_me' => true,
        ]);

        $loginResponse->assertStatus(200);

        $firstRefreshToken = $loginResponse->json('data.refresh_token');

        $this->assertNotEmpty($firstRefreshToken);

        $session = AuthSession::where('user_id', $user->id)
            ->first();

        $this->assertNotNull($session);

        // First refresh: rotate the original token.
        $firstRefreshResponse = $this->withToken($firstRefreshToken)
            ->postJson('/api/auth/refresh');

        $firstRefreshResponse->assertStatus(200);

        $secondRefreshToken = $firstRefreshResponse->json('data.refresh_token');

        $this->assertNotEmpty($secondRefreshToken);
        $this->assertNotEquals($firstRefreshToken, $secondRefreshToken);

        // Verify that the first token has been consumed.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $firstRefreshToken),
        ]);

        // Reuse the original refresh token.
        $replayResponse = $this->withToken($firstRefreshToken)
            ->postJson('/api/auth/refresh');

        $replayResponse->assertStatus(401);

        // The session must be revoked after detecting token reuse.
        $this->assertDatabaseHas('auth_sessions', [
            'id' => $session->id,
        ]);

        $session->refresh();

        $this->assertNotNull($session->revoked_at);

        // All refresh tokens belonging to this session must be revoked.
        $this->assertDatabaseMissing('refresh_tokens', [
            'session_id' => $session->id,
            'revoked_at' => null,
        ]);
    }

    public function test_user_can_verify_email_with_valid_link()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect(
            config('services.frontend_url') . '/auth/email-verified?status=success'
        );

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

}