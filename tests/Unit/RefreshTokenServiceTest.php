<?php

namespace Tests\Unit;

use App\Http\Services\Auth\RefreshTokenService;
use App\Models\User\AuthSession;
use App\Models\User\RefreshToken;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_session_and_stores_only_the_refresh_token_hash(): void
    {
        $user = User::factory()->create();

        $service = app(RefreshTokenService::class);

        $result = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit Test'
        );

        // The session should be created.
        $this->assertDatabaseHas('auth_sessions', [
            'id' => $result['session']->id,
            'user_id' => $user->id,
            'client_type' => 'web',
            'remember_me' => true,
        ]);

        // The refresh token should be stored as a SHA-256 hash.
        $this->assertDatabaseHas('refresh_tokens', [
            'session_id' => $result['session']->id,
            'token_hash' => hash('sha256', $result['refresh_token']),
        ]);

        // The raw token should never be stored in the database.
        $this->assertDatabaseMissing('refresh_tokens', [
            'token_hash' => $result['refresh_token'],
        ]);

        // The raw refresh token should contain 128 hexadecimal characters.
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{128}$/',
            $result['refresh_token']
        );

        // Remember Me should create a 30-day session.
        $this->assertEquals(
            now()->addDays(30)->timestamp,
            $result['expires_at']->timestamp
        );

        // Exactly one session and one refresh token should exist.
        $this->assertSame(1, AuthSession::count());
        $this->assertSame(1, RefreshToken::count());
    }


    public function test_it_rotates_a_refresh_token_successfully(): void
    {
        $user = User::factory()->create();

        $service = app(RefreshTokenService::class);

        $initialSession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        $oldToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $initialSession['refresh_token'])
        )->firstOrFail();

        $originalExpiry = $initialSession['session']->expires_at->timestamp;

        $result = $service->rotate($initialSession['refresh_token']);

        $newToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $result['refresh_token'])
        )->firstOrFail();

        // The old token should be consumed and revoked.
        $this->assertNotNull($oldToken->fresh()->used_at);
        $this->assertNotNull($oldToken->fresh()->revoked_at);

        // The new token should be active.
        $this->assertNull($newToken->revoked_at);
        $this->assertNull($newToken->used_at);

        // The old token should reference the new token.
        $this->assertEquals(
            $newToken->id,
            $oldToken->fresh()->replaced_by_token_id
        );

        // Both tokens should belong to the same session.
        $this->assertEquals(
            $initialSession['session']->id,
            $newToken->session_id
        );

        // The session expiry should remain unchanged.
        $this->assertEquals(
            $originalExpiry,
            $result['session']->expires_at->timestamp
        );

        // The session should remain active.
        $this->assertNull($result['session']->revoked_at);

        // The session should have two refresh token records.
        $this->assertSame(2, RefreshToken::count());
    }


    public function test_it_rejects_invalid_expired_and_revoked_refresh_tokens(): void
    {
        $service = app(RefreshTokenService::class);

        // 1. Invalid token.
        try {
            $service->rotate(str_repeat('a', 128));
            $this->fail('Expected an exception for an invalid token.');
        } catch (\RuntimeException $e) {
            $this->assertSame(401, $e->getCode());
        }

        $user = User::factory()->create();

        $sessionData = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        $token = RefreshToken::where(
            'token_hash',
            hash('sha256', $sessionData['refresh_token'])
        )->firstOrFail();

        // 2. Expired token.
        $token->update([
            'expires_at' => now()->subMinute(),
        ]);

        try {
            $service->rotate($sessionData['refresh_token']);
            $this->fail('Expected an exception for an expired token.');
        } catch (\RuntimeException $e) {
            $this->assertSame(401, $e->getCode());
        }

        // 3. Revoked token.
        $revokedSession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        $revokedToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $revokedSession['refresh_token'])
        )->firstOrFail();

        $revokedToken->update([
            'revoked_at' => now(),
        ]);

        try {
            $service->rotate($revokedSession['refresh_token']);
            $this->fail('Expected an exception for a revoked token.');
        } catch (\RuntimeException $e) {
            $this->assertSame(401, $e->getCode());
        }
    }


    public function test_it_revokes_the_entire_session_when_a_refresh_token_is_reused(): void
    {
        $user = User::factory()->create();

        $service = app(RefreshTokenService::class);

        // Create the initial session and refresh token.
        $initialSession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        $oldToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $initialSession['refresh_token'])
        )->firstOrFail();

        // Rotate the initial token successfully.
        $rotationResult = $service->rotate(
            $initialSession['refresh_token']
        );

        $newToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $rotationResult['refresh_token'])
        )->firstOrFail();

        // Try to reuse the old token.
        try {
            $service->rotate($initialSession['refresh_token']);

            $this->fail('Expected an exception when reusing an old refresh token.');
        } catch (\RuntimeException $e) {
            $this->assertSame(401, $e->getCode());
        }

        // The entire session should be revoked.
        $this->assertNotNull(
            $initialSession['session']->fresh()->revoked_at
        );

        // Both the old and new tokens should be revoked.
        $this->assertNotNull($oldToken->fresh()->revoked_at);
        $this->assertNotNull($newToken->fresh()->revoked_at);

        // The new token should no longer be usable.
        $this->assertNotNull($newToken->fresh()->revoked_at);
    }


    public function test_it_revokes_only_the_requested_session(): void
    {
        $user = User::factory()->create();

        $service = app(RefreshTokenService::class);

        // Create the first session.
        $firstSession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        // Create the second session for the same user.
        $secondSession = $service->createSession(
            user: $user,
            clientType: 'mobile',
            rememberMe: true
        );

        $firstToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $firstSession['refresh_token'])
        )->firstOrFail();

        $secondToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $secondSession['refresh_token'])
        )->firstOrFail();

        // Revoke only the first session.
        $service->revokeSession($firstSession['session']);

        // The first session and its token should be revoked.
        $this->assertNotNull(
            $firstSession['session']->fresh()->revoked_at
        );

        $this->assertNotNull($firstToken->fresh()->revoked_at);

        // The second session and its token should remain active.
        $this->assertNull(
            $secondSession['session']->fresh()->revoked_at
        );

        $this->assertNull($secondToken->fresh()->revoked_at);

        // Verify that the second token can still be rotated.
        $result = $service->rotate($secondSession['refresh_token']);

        $this->assertNotEmpty($result['refresh_token']);
        $this->assertNull($result['session']->fresh()->revoked_at);
    }


    public function test_it_sets_session_expiration_based_on_remember_me(): void
    {
        $user = User::factory()->create();

        $service = app(RefreshTokenService::class);

        $rememberedSession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: true
        );

        $temporarySession = $service->createSession(
            user: $user,
            clientType: 'web',
            rememberMe: false
        );

        // Remember Me: 30 days.
        $this->assertEquals(
            now()->addDays(30)->timestamp,
            $rememberedSession['expires_at']->timestamp
        );

        // Without Remember Me: 1 day.
        $this->assertEquals(
            now()->addDay()->timestamp,
            $temporarySession['expires_at']->timestamp
        );

        // Refresh token expiration must match its session expiration.
        $rememberedToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $rememberedSession['refresh_token'])
        )->firstOrFail();

        $temporaryToken = RefreshToken::where(
            'token_hash',
            hash('sha256', $temporarySession['refresh_token'])
        )->firstOrFail();

        $this->assertEquals(
            $rememberedSession['expires_at']->timestamp,
            $rememberedToken->expires_at->timestamp
        );

        $this->assertEquals(
            $temporarySession['expires_at']->timestamp,
            $temporaryToken->expires_at->timestamp
        );
    }

}
