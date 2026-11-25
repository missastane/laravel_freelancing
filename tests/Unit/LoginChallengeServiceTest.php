<?php

namespace Tests\Unit;

use App\Http\Services\Auth\LoginChallengeService;
use App\Models\User\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LoginChallengeServiceTest extends TestCase
{
    private LoginChallengeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('redis')->flush();

        $this->service = app(LoginChallengeService::class);
    }

    protected function tearDown(): void
    {
        Cache::store('redis')->flush();

        parent::tearDown();
    }

    public function test_it_creates_a_login_challenge(): void
    {
        $user = new User();
        $user->id = 15;

        $token = $this->service->create(
            user: $user,
            clientType: 'web',
            rememberMe: true,
            ipAddress: '127.0.0.1',
            userAgent: 'Chrome'
        );

        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));

        $challenge = $this->service->getChallenge($token);

        $this->assertNotNull($challenge);
        $this->assertSame(15, $challenge['user_id']);
        $this->assertSame('web', $challenge['client_type']);
        $this->assertTrue($challenge['remember_me']);
        $this->assertSame('127.0.0.1', $challenge['ip_address']);
        $this->assertSame('Chrome', $challenge['user_agent']);
        $this->assertSame(0, $challenge['attempts']);
    }

    public function test_it_returns_null_for_an_invalid_challenge(): void
    {
        $challenge = $this->service->getChallenge('invalid-token');

        $this->assertNull($challenge);
    }

    public function test_it_records_failed_attempts(): void
    {
        $user = new User();
        $user->id = 15;

        $token = $this->service->create(
            user: $user,
            clientType: 'web',
            rememberMe: false
        );

        $result = $this->service->recordFailedAttempt($token);

        $this->assertTrue($result);

        $challenge = $this->service->getChallenge($token);

        $this->assertSame(1, $challenge['attempts']);
    }

    public function test_it_expires_a_challenge_after_five_failed_attempts(): void
    {
        $user = new User();
        $user->id = 15;

        $token = $this->service->create(
            user: $user,
            clientType: 'web',
            rememberMe: false
        );

        for ($i = 0; $i < 5; $i++) {
            $this->service->recordFailedAttempt($token);
        }

        $this->assertNull($this->service->getChallenge($token));
    }

    public function test_it_consumes_a_challenge_only_once(): void
    {
        $user = new User();
        $user->id = 15;

        $token = $this->service->create(
            user: $user,
            clientType: 'web',
            rememberMe: false
        );

        $firstConsumption = $this->service->consume($token);
        $secondConsumption = $this->service->consume($token);

        $this->assertNotNull($firstConsumption);
        $this->assertSame(15, $firstConsumption['user_id']);
        $this->assertNull($secondConsumption);
    }

    public function test_it_does_not_consume_an_invalid_challenge(): void
    {
        $result = $this->service->consume('invalid-token');

        $this->assertNull($result);
    }


    public function test_it_verifies_and_consumes_a_valid_challenge(): void
    {
        $user = User::factory()->create();

        $token = $this->service->create(
            $user,
            'web',
            false
        );

        $result = $this->service->verifyAndConsume(
            $token,
            fn(array $challenge) => $challenge['user_id'] === $user->id
        );

        $this->assertSame('verified', $result['status']);
        $this->assertSame($user->id, $result['challenge']['user_id']);

        $this->assertNull($this->service->getChallenge($token));
    }

    public function test_it_records_failed_attempt_when_verification_fails(): void
    {
        $user = User::factory()->create();

        $token = $this->service->create(
            $user,
            'web',
            false
        );

        $result = $this->service->verifyAndConsume(
            $token,
            fn() => false
        );

        $this->assertSame('invalid_code', $result['status']);
        $this->assertSame(4, $result['remaining_attempts']);

        $challenge = $this->service->getChallenge($token);

        $this->assertNotNull($challenge);
        $this->assertSame(1, $challenge['attempts']);
    }

    public function test_it_expires_challenge_after_five_failed_verification_attempts(): void
    {
        $user = User::factory()->create();

        $token = $this->service->create(
            $user,
            'web',
            false
        );

        for ($i = 1; $i <= 5; $i++) {
            $result = $this->service->verifyAndConsume(
                $token,
                fn() => false
            );
        }

        $this->assertSame('invalid_code', $result['status']);
        $this->assertSame(0, $result['remaining_attempts']);

        $this->assertNull($this->service->getChallenge($token));
    }

    public function test_it_does_not_run_verification_for_invalid_challenge(): void
    {
        $callbackExecuted = false;

        $result = $this->service->verifyAndConsume(
            'invalid-token',
            function () use (&$callbackExecuted) {
                $callbackExecuted = true;

                return true;
            }
        );

        $this->assertSame('invalid_challenge', $result['status']);
        $this->assertFalse($callbackExecuted);
    }

    public function test_it_does_not_run_verification_for_expired_challenge(): void
    {
        $user = User::factory()->create();

        $token = $this->service->create(
            $user,
            'web',
            false
        );

        // Simulate expiration.
        $this->travel(301)->seconds();

        $callbackExecuted = false;

        $result = $this->service->verifyAndConsume(
            $token,
            function () use (&$callbackExecuted) {
                $callbackExecuted = true;

                return true;
            }
        );

        $this->assertSame('invalid_challenge', $result['status']);
        $this->assertFalse($callbackExecuted);

        $this->assertNull($this->service->getChallenge($token));
    }

    public function test_it_rejects_reusing_a_successfully_consumed_challenge(): void
    {
        $user = User::factory()->create();

        $token = $this->service->create(
            $user,
            'web',
            false
        );

        $firstResult = $this->service->verifyAndConsume(
            $token,
            fn() => true
        );

        $secondResult = $this->service->verifyAndConsume(
            $token,
            fn() => true
        );

        $this->assertSame('verified', $firstResult['status']);
        $this->assertSame('invalid_challenge', $secondResult['status']);
    }

}
