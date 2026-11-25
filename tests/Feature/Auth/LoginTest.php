<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User\AuthSession;
use App\Models\User\User;
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
}