<?php

namespace Tests\Feature\Auth;

use App\Models\User\AuthSession;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login and create a real authentication session.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
        ]);

        $loginResponse->assertStatus(200);

        $accessToken = $loginResponse->json('data.access_token');

        $refreshToken = $loginResponse->json('data.refresh_token');

        $session = AuthSession::where('user_id', $user->id)
            ->latest()
            ->first();

        $this->assertNotNull($session);

        // Logout.
        $response = $this->withToken($accessToken)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => null,
            ]);

        // The authentication session must be revoked.
        $this->assertNotNull($session->fresh()->revoked_at);

        // All refresh tokens belonging to this session must be revoked.
        $this->assertDatabaseMissing('refresh_tokens', [
            'session_id' => $session->id,
            'revoked_at' => null,
        ]);

        // The old refresh token must no longer work.
        $refreshResponse = $this->withToken($refreshToken)
            ->postJson('/api/auth/refresh');

        $refreshResponse->assertStatus(401);

        // The old access token must no longer work.
        $protectedResponse = $this->withToken($accessToken)
            ->getJson('/api/protected-route');

        $protectedResponse->assertStatus(401);
    }

    public function test_logout_only_revokes_current_device_session()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login from the first device.
        $firstLogin = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
        ]);

        $firstLogin->assertStatus(200);

        $firstAccessToken = $firstLogin->json('data.access_token');
        $firstRefreshToken = $firstLogin->json('data.refresh_token');

        // Login from the second device.
        $secondLogin = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'mobile',
        ]);

        $secondLogin->assertStatus(200);

        $secondAccessToken = $secondLogin->json('data.access_token');
        $secondRefreshToken = $secondLogin->json('data.refresh_token');

        // Retrieve both independent sessions.
        $sessions = AuthSession::where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $sessions);

        $firstSession = $sessions[0];
        $secondSession = $sessions[1];

        // Logout only from the first device.
        $logoutResponse = $this->withToken($firstAccessToken)
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // First session must be revoked.
        $this->assertNotNull($firstSession->fresh()->revoked_at);

        // Second session must remain active.
        $this->assertNull($secondSession->fresh()->revoked_at);

        // First device's refresh token must be unusable.
        $firstRefreshResponse = $this->withToken($firstRefreshToken)
            ->postJson('/api/auth/refresh');

        $firstRefreshResponse->assertStatus(401);

        // Second device's refresh token must still work.
        $secondRefreshResponse = $this->withToken($secondRefreshToken)
            ->postJson('/api/auth/refresh');

        $secondRefreshResponse->assertStatus(200);

        // Second session must still be active after refresh.
        $this->assertNull($secondSession->fresh()->revoked_at);
    }

    public function test_web_user_can_logout_and_clear_refresh_cookie()
    {
        $user = User::factory()->create([
            'password' => bcrypt('123Ss!45678'),
        ]);

        // Login as a web user.
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123Ss!45678',
            'client_type' => 'web',
        ]);

        $loginResponse->assertStatus(200);

        $accessToken = $loginResponse->json('data.access_token');

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

        // Verify cookie security attributes.
        $this->assertTrue($refreshCookie->isHttpOnly());
        $this->assertFalse($csrfCookie->isHttpOnly());

        // Retrieve the authentication session.
        $session = AuthSession::where('user_id', $user->id)
            ->latest()
            ->first();

        $this->assertNotNull($session);

        // Logout.
        $logoutResponse = $this->withToken($accessToken)
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // Retrieve logout cookies.
        $logoutCookies = collect($logoutResponse->headers->getCookies());

        $expiredRefreshCookie = $logoutCookies->first(
            fn($cookie) => $cookie->getName() === 'refresh_token'
        );

        $expiredCsrfCookie = $logoutCookies->first(
            fn($cookie) => $cookie->getName() === 'XSRF-TOKEN'
        );

        // Both cookies must be expired.
        $this->assertNotNull($expiredRefreshCookie);
        $this->assertNotNull($expiredCsrfCookie);

        $this->assertLessThanOrEqual(
            0,
            $expiredRefreshCookie->getMaxAge()
        );

        $this->assertLessThanOrEqual(
            0,
            $expiredCsrfCookie->getMaxAge()
        );

        // Cookie security attributes must remain consistent.
        $this->assertTrue($expiredRefreshCookie->isHttpOnly());
        $this->assertFalse($expiredCsrfCookie->isHttpOnly());

        // Verify that the authentication session is revoked.
        $this->assertNotNull($session->fresh()->revoked_at);

        // Verify that all refresh tokens are revoked.
        $this->assertDatabaseMissing('refresh_tokens', [
            'session_id' => $session->id,
            'revoked_at' => null,
        ]);
    }
}