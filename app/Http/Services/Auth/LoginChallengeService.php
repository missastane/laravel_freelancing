<?php

namespace App\Http\Services\Auth;

use App\Models\User\User;
use Illuminate\Support\Facades\Cache;

class LoginChallengeService
{
    private const TTL_SECONDS = 300; // 5 minutes

    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 30;

    private function cache()
    {
        return Cache::store('redis');
    }

    private function getCacheKey(string $token): string
    {
        return 'auth:login_challenges:' . hash('sha256', $token);
    }

    private function getLockKey(string $token): string
    {
        return 'auth:login_challenges:lock:' . hash('sha256', $token);
    }

    /**
     * Create a new login challenge.
     */
    public function create(
        User $user,
        string $clientType,
        bool $rememberMe,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): string {
        $token = bin2hex(random_bytes(32));

        $this->cache()->put(
            $this->getCacheKey($token),
            [
                'user_id' => $user->id,
                'client_type' => $clientType,
                'remember_me' => $rememberMe,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'attempts' => 0,
                'expires_at' => now()->addSeconds(self::TTL_SECONDS)->timestamp,
            ],
            self::TTL_SECONDS
        );

        return $token;
    }

    /**
     * Retrieve challenge data without consuming it.
     */
    public function getChallenge(string $token): ?array
    {
        $key = $this->getCacheKey($token);

        $challenge = $this->cache()->get($key);

        if (!$challenge || $challenge['expires_at'] <= now()->timestamp) {
            $this->cache()->forget($key);

            return null;
        }

        if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
            $this->cache()->forget($key);

            return null;
        }

        return $challenge;
    }

    public function getUserIdByToken(string $token): ?int
    {
        $challenge = $this->cache()->get($this->getCacheKey($token));

        if (
            !$challenge ||
            $challenge['expires_at'] <= now()->timestamp ||
            $challenge['attempts'] >= self::MAX_ATTEMPTS
        ) {
            return null;
        }

        return (int) $challenge['user_id'];
    }

    /**
     * Record a failed verification attempt.
     *
     * Returns false when the challenge is expired,
     * invalid, or has reached its maximum attempts.
     */
    public function recordFailedAttempt(string $token): bool
    {
        $cache = $this->cache();

        return $cache->lock(
            $this->getLockKey($token),
            self::LOCK_SECONDS
        )->block(3, function () use ($cache, $token) {
            $key = $this->getCacheKey($token);

            $challenge = $cache->get($key);

            if (
                !$challenge ||
                $challenge['expires_at'] <= now()->timestamp
            ) {
                $cache->forget($key);

                return false;
            }

            $challenge['attempts']++;

            if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                $cache->forget($key);

                return false;
            }

            $remainingSeconds = max(
                1,
                $challenge['expires_at'] - now()->timestamp
            );

            $cache->put($key, $challenge, $remainingSeconds);

            return true;
        });
    }

    /**
     * Verify the second factor and consume the challenge atomically
     * under a shared Redis lock.
     *
     * The callback receives the challenge data and must return
     * true when the second-factor verification succeeds.
     *
     * Possible statuses:
     * - invalid_challenge
     * - invalid_code
     * - verified
     */
    public function verifyAndConsume(
        string $token,
        callable $verify
    ): array {
        $cache = $this->cache();

        return $cache->lock(
            $this->getLockKey($token),
            self::LOCK_SECONDS
        )->block(3, function () use ($cache, $token, $verify) {
            $key = $this->getCacheKey($token);

            $challenge = $cache->get($key);

            // Check challenge validity.
            if (
                !$challenge ||
                $challenge['expires_at'] <= now()->timestamp ||
                $challenge['attempts'] >= self::MAX_ATTEMPTS
            ) {
                $cache->forget($key);

                return [
                    'status' => 'invalid_challenge',
                ];
            }

            // Verify the second factor while holding the lock.
            $isValid = (bool) $verify($challenge);

            if (!$isValid) {
                $challenge['attempts']++;

                if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                    $cache->forget($key);

                    return [
                        'status' => 'invalid_code',
                        'remaining_attempts' => 0,
                        'attempts_exceeded' => true,
                        'challenge' => $challenge,
                    ];
                }

                $remainingSeconds = max(
                    1,
                    $challenge['expires_at'] - now()->timestamp
                );

                $cache->put($key, $challenge, $remainingSeconds);

                return [
                    'status' => 'invalid_code',
                    'remaining_attempts' => self::MAX_ATTEMPTS - $challenge['attempts'],
                ];
            }

            // Consume the challenge after successful verification.
            $cache->forget($key);

            return [
                'status' => 'verified',
                'challenge' => $challenge,
            ];
        });
    }

    /**
     * Consume a challenge exactly once.
     */
    public function consume(string $token): ?array
    {
        $cache = $this->cache();

        return $cache->lock(
            $this->getLockKey($token),
            self::LOCK_SECONDS
        )->block(3, function () use ($cache, $token) {
            $key = $this->getCacheKey($token);

            $challenge = $cache->get($key);

            if (
                !$challenge ||
                $challenge['expires_at'] <= now()->timestamp ||
                $challenge['attempts'] >= self::MAX_ATTEMPTS
            ) {
                $cache->forget($key);

                return null;
            }

            $cache->forget($key);

            return $challenge;
        });
    }
}
