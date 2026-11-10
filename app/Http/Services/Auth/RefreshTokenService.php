<?php

namespace App\Http\Services\Auth;

use App\Models\User\AuthSession;
use App\Models\User\RefreshToken;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefreshTokenService
{
    private const REMEMBER_DURATION_DAYS = 30;

    // Server-side fallback for sessions without Remember Me.
    private const SESSION_DURATION_DAYS = 1;

    /**
     * Create a new authentication session and its first refresh token.
     */
    public function createSession(
        User $user,
        string $clientType,
        bool $rememberMe,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        $expiresAt = now()->addDays(
            $rememberMe
            ? self::REMEMBER_DURATION_DAYS
            : self::SESSION_DURATION_DAYS
        );

        $plainToken = $this->generateToken();

        $session = DB::transaction(function () use ($user, $clientType, $rememberMe, $ipAddress, $userAgent, $expiresAt, $plainToken) {
            $session = AuthSession::create([
                'user_id' => $user->id,
                'client_type' => $clientType,
                'remember_me' => $rememberMe,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'expires_at' => $expiresAt,
            ]);

            $session->refreshTokens()->create([
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => $expiresAt,
            ]);

            return $session;
        });

        return [
            'session' => $session,
            'refresh_token' => $plainToken,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Validate and rotate a refresh token.
     */
    public function rotate(string $plainToken, ?string $expectedClientType = null): array
    {
        $tokenHash = hash('sha256', $plainToken);

        $result = DB::transaction(function () use ($tokenHash, $expectedClientType) {
            $token = RefreshToken::where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (!$token) {
                return ['error' => 'invalid'];
            }

            $session = AuthSession::whereKey($token->session_id)
                ->lockForUpdate()
                ->first();

            if (!$session) {
                return ['error' => 'invalid'];
            }

            if (
                $expectedClientType !== null &&
                $session->client_type !== $expectedClientType
            ) {
                return ['error' => 'invalid'];
            }

            // A previously used token is being reused.
            if ($token->used_at !== null) {
                $now = now();

                $session->refreshTokens()
                    ->whereNull('revoked_at')
                    ->update([
                        'revoked_at' => $now,
                        'updated_at' => $now,
                    ]);

                $session->update([
                    'revoked_at' => $now,
                ]);

                return ['error' => 'replay'];
            }

            if (
                $token->revoked_at !== null ||
                !$session->isActive() ||
                $token->expires_at->isPast()
            ) {
                return ['error' => 'invalid'];
            }

            $newPlainToken = $this->generateToken();
            $now = now();

            $newToken = $session->refreshTokens()->create([
                'token_hash' => hash('sha256', $newPlainToken),
                'expires_at' => $session->expires_at,
            ]);

            $token->update([
                'used_at' => $now,
                'revoked_at' => $now,
                'replaced_by_token_id' => $newToken->id,
            ]);

            $session->update([
                'last_used_at' => $now,
            ]);

            return [
                'session' => $session,
                'refresh_token' => $newPlainToken,
                'expires_at' => $session->expires_at,
            ];
        });

        if (($result['error'] ?? null) === 'replay') {
            throw new RuntimeException(
                'Refresh token reuse detected. Session has been revoked.',
                401
            );
        }

        if (($result['error'] ?? null) === 'invalid') {
            throw new RuntimeException(
                'Invalid or expired refresh token.',
                401
            );
        }

        return $result;
    }

    /**
     * Revoke an authentication session and its active refresh tokens.
     */
    public function revokeSession(AuthSession $session): void
    {
        DB::transaction(function () use ($session) {
            $session = AuthSession::whereKey($session->id)
                ->lockForUpdate()
                ->first();

            if (!$session) {
                return;
            }

            $now = now();

            $session->refreshTokens()
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($session->revoked_at === null) {
                $session->update([
                    'revoked_at' => $now,
                ]);
            }
        });
    }

    /**
     * Generate a cryptographically secure random token.
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }
}
