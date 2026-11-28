<?php

namespace App\Http\Services\User;

use App\Events\TwoFactorLoginAttemptsExceeded;
use App\Http\Services\Auth\RefreshTokenService;
use App\Jobs\ResetPasswordJob;
use App\Jobs\SendEmailChangeVerification;
use App\Jobs\SendVerificationEmail;
use App\Models\User\AuthSession;
use App\Models\User\EmailChangeRequest;
use App\Models\User\User;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Services\Auth\LoginChallengeService;
use App\Http\Services\Auth\TwoFactorService;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected RoleRepositoryInterface $roleRepository,
        protected RefreshTokenService $refreshTokenService,
        protected LoginChallengeService $loginChallengeService,
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function registerUser(array $data)
    {
        $username = Str::random(16);

        $user = DB::transaction(function () use ($username, $data) {
            $user = $this->userRepository->create([
                'username' => $username,
                'user_type' => 1,
                'email' => $data['email'],
                'active_role' => $data['role'] == 1 ? 'employer' : 'freelancer',
                'password' => Hash::make($data['password']),
            ]);

            $this->roleRepository->firstOrCreate($user->active_role);
            $user->assignRole($user->active_role);

            $this->walletRepository->create([
                'user_id' => $user->id,
                'balance' => 0,
                'hold_balance' => 0,
                'currency' => 1,
            ]);

            return $user;
        });

        dispatch(new SendVerificationEmail($user));

        return $user;
    }

    public function loginUser(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'status' => false,
                'message' => __('auth.failed'),
                'code' => 401,
            ];
        }

        $clientType = $data['client_type'] ?? 'web';
        $rememberMe = (bool) ($data['remember_me'] ?? false);

        if (!$user->hasVerifiedEmail()) {
            return [
                'status' => false,
                'message' => __('errors.email_has_not_verified'),
                'code' => 403,
            ];
        }

        // Require two-factor authentication before creating a session.
        if ($user->twoFactorAuth?->isEnabled()) {
            $challengeToken = $this->loginChallengeService->create(
                user: $user,
                clientType: $clientType,
                rememberMe: $rememberMe,
                ipAddress: $data['ip_address'] ?? null,
                userAgent: $data['user_agent'] ?? null
            );

            return [
                'status' => true,
                'message' => __('messages.two_factor_required'),
                'data' => [
                    'requires_two_factor' => true,
                    'challenge_token' => $challengeToken,
                    'expires_in' => 300,
                ],
                'code' => 200,
            ];
        }

        // Create a new authentication session and refresh token.
        $sessionData = $this->refreshTokenService->createSession(
            user: $user,
            clientType: $clientType,
            rememberMe: $rememberMe,
            ipAddress: $data['ip_address'] ?? null,
            userAgent: $data['user_agent'] ?? null
        );

        // Generate an access token linked to this session.
        $token = JWTAuth::claims([
            'sid' => $sessionData['session']->id,
        ])->fromUser($user);

        return [
            'status' => true,
            'message' => __('messages.login_success', [
                'user' => $user->full_name,
            ]),
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'refresh_token' => $sessionData['refresh_token'],
            ],
            'code' => 200,
        ];
    }

    /**
     * Complete login after successful two-factor authentication.
     */
    public function completeTwoFactorLogin(
        string $challengeToken,
        string $code
    ): array {
        $user = null;
        $failureReason = null;

        $result = $this->loginChallengeService->verifyAndConsume(
            $challengeToken,
            function (array $challenge) use ($code, &$user, &$failureReason): bool {
                $user = User::find($challenge['user_id']);

                if (!$user) {
                    $failureReason = 'invalid_challenge';

                    return false;
                }

                if (!$user->hasVerifiedEmail()) {
                    $failureReason = 'email_unverified';

                    return false;
                }

                if (!$user->twoFactorAuth?->isEnabled()) {
                    $failureReason = 'two_factor_disabled';

                    return false;
                }

                return $this->twoFactorService->verifyLoginCode(
                    $user,
                    $code
                );
            }
        );

        if ($result['status'] === 'invalid_challenge') {
            return [
                'status' => false,
                'message' => __('auth.failed'),
                'code' => 401,
            ];
        }

        if ($result['status'] === 'invalid_code') {

            // The user associated with the challenge no longer exists.
            if ($failureReason === 'invalid_challenge') {
                return [
                    'status' => false,
                    'message' => __('auth.failed'),
                    'code' => 401,
                ];
            }

            if ($failureReason === 'email_unverified') {
                return [
                    'status' => false,
                    'message' => __('errors.email_has_not_verified'),
                    'code' => 403,
                ];
            }

            if ($failureReason === 'two_factor_disabled') {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_not_enabled'),
                    'code' => 409,
                ];
            }

            if ($result['attempts_exceeded'] ?? false) {
                event(new TwoFactorLoginAttemptsExceeded(
                    user: $user,
                    challenge: $result['challenge']
                ));
            }

            $message = __('errors.two_factor_code_invalid');
            $message .= ' ' . __('errors.two_factor_attempts_remaining', [
                'remaining' => $result['remaining_attempts'],
            ]);

            return [
                'status' => false,
                'message' => $message,
                'code' => 422,
            ];
        }

        // The challenge has been consumed successfully.
        $challenge = $result['challenge'];

        // Create the authentication session and refresh token.
        $sessionData = $this->refreshTokenService->createSession(
            user: $user,
            clientType: $challenge['client_type'],
            rememberMe: $challenge['remember_me'],
            ipAddress: $challenge['ip_address'],
            userAgent: $challenge['user_agent']
        );

        // Generate an access token linked to this session.
        $token = JWTAuth::claims([
            'sid' => $sessionData['session']->id,
        ])->fromUser($user);

        return [
            'status' => true,
            'message' => __('messages.login_success', [
                'user' => $user->full_name,
            ]),
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'refresh_token' => $sessionData['refresh_token'],
                'client_type' => $challenge['client_type'],
                'remember_me' => $challenge['remember_me'],
            ],
            'code' => 200,
        ];
    }

    public function logoutUser(): void
    {
        $token = JWTAuth::getToken();

        if (!$token) {
            throw new \RuntimeException('Unauthenticated.', 401);
        }

        // Read the session ID from the current access token.
        $payload = JWTAuth::getPayload();
        $sessionId = $payload->get('sid');

        $userId = auth()->id();

        if (!$sessionId || !$userId) {
            throw new \RuntimeException('Invalid authentication session.', 401);
        }

        DB::transaction(function () use ($sessionId, $userId) {
            $session = AuthSession::whereKey($sessionId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($session && $session->revoked_at === null) {
                $now = now();

                // Revoke all active refresh tokens of this session.
                $session->refreshTokens()
                    ->whereNull('revoked_at')
                    ->update([
                        'revoked_at' => $now,
                        'updated_at' => $now,
                    ]);

                // Revoke the current authentication session.
                $session->update([
                    'revoked_at' => $now,
                ]);
            }
        });

        // Invalidate the current access token.
        JWTAuth::invalidate($token);
    }

    public function refresh(
        string $plainRefreshToken,
        string $clientType
    ): array {
        // Validate and rotate the refresh token.
        $result = $this->refreshTokenService->rotate(
            $plainRefreshToken,
            $clientType
        );

        $session = $result['session'];

        // Generate a new access token linked to the existing session.
        $accessToken = JWTAuth::claims([
            'sid' => $session->id,
        ])->fromUser($session->user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $result['refresh_token'],
            'session' => $session,
        ];
    }

    public function resendVerificationEmail(array $data): bool
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if ($user && !$user->hasVerifiedEmail()) {
            dispatch(new SendVerificationEmail($user));
        }

        return true;
    }

    public function checkVerificationStatus($user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'status' => true,
                'message' => __('errors.email_has_verified'),
                'code' => 200,
            ];
        }

        return [
            'status' => false,
            'message' => __('errors.email_has_not_verified'),
            'code' => 403,
        ];
    }

    public function verifyEmail(array $data, $id, string $hash): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return [
                'status' => false,
                'message' => __('errors.email_verification_invalid'),
                'code' => 403,
            ];
        }

        if ($user->hasVerifiedEmail()) {
            return [
                'status' => false,
                'message' => __('errors.email_has_verified'),
                'code' => 400,
            ];
        }

        $user->markEmailAsVerified();

        $this->userRepository->update($user, [
            'activation' => 1,
            'activation_date' => now(),
        ]);

        event(new Verified($user));

        return [
            'status' => true,
            'message' => __('messages.email_verified'),
            'data' => null,
            'code' => 200,
        ];
    }

    public function forgotPassword(array $data): bool
    {
        $email = strtolower(trim($data['email']));
        $key = "reset-password-throttle:{$email}";

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        RateLimiter::hit($key, 60 * 30);

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return true;
        }

        $token = Password::createToken($user);

        ResetPasswordJob::dispatch($user, $token);

        return true;
    }

    public function resetPassword(array $data): array
    {
        try {
            $status = Password::reset(
                $data,
                function ($user, $password) {
                    DB::transaction(function () use ($user, $password) {
                        if ($user->email_verified_at === null) {
                            $this->userRepository->verifyUser($user);
                        }
                        $this->userRepository->updatePassword($user, Hash::make($password));
                    });
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return [
                    'status' => true,
                    'message' => __('messages.success_changed_password'),
                    'code' => 200,
                ];
            }

            return [
                'status' => false,
                'message' => __('auth.failed'),
                'code' => 400,
            ];
        } catch (Exception $e) {
            Log::error('Reset password method failed: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => __('errors.server_error'),
                'code' => 500,
            ];
        }
    }

    public function getMe()
    {
        $user = auth()->user();
        return $user;
    }


    public function requestEmailChange(
        User $user,
        string $newEmail,
        string $currentPassword
    ): array {
        // Verify the user's current password.
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'status' => false,
                'message' => __('auth.failed'),
                'code' => 403,
            ];
        }

        $newEmail = strtolower(trim($newEmail));

        // Prevent requesting the current email address.
        if (strtolower($user->email) === $newEmail) {
            return [
                'status' => false,
                'message' => __('validation.different', [
                    'attribute' => 'email',
                    'other' => 'current email',
                ]),
                'code' => 422,
            ];
        }

        // Check whether the email belongs to another user.
        if ($this->userRepository->emailExists($newEmail, $user->id)) {
            return [
                'status' => false,
                'message' => __('validation.unique', [
                    'attribute' => 'email',
                ]),
                'code' => 422,
            ];
        }

        // Prevent multiple users from having the same active pending email.
        $pendingEmailExists = EmailChangeRequest::where(
            'new_email',
            $newEmail
        )
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($pendingEmailExists) {
            return [
                'status' => false,
                'message' => __('validation.unique', [
                    'attribute' => 'email',
                ]),
                'code' => 422,
            ];
        }

        $plainToken = Str::random(64);

        $emailChangeRequest = DB::transaction(function () use ($user, $newEmail, $plainToken) {
            // Remove previous unverified requests of this user.
            EmailChangeRequest::where('user_id', $user->id)
                ->whereNull('verified_at')
                ->delete();

            return EmailChangeRequest::create([
                'user_id' => $user->id,
                'new_email' => $newEmail,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addMinutes(30),
            ]);
        });

        // Dispatch the verification email after the transaction commits.
        // Email job will be implemented separately.
        DB::afterCommit(function () use ($newEmail, $plainToken) {
            SendEmailChangeVerification::dispatch(
                $newEmail,
                $plainToken
            );
        });

        return [
            'status' => true,
            'message' => __('messages.email_change_verification_sent'),
            'data' => null,
            'code' => 200,
        ];
    }


    public function verifyEmailChange(string $plainToken): array
    {
        $tokenHash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($tokenHash) {

            // Find and lock the email change request.
            $emailChangeRequest = EmailChangeRequest::where(
                'token_hash',
                $tokenHash
            )
                ->lockForUpdate()
                ->first();

            if (!$emailChangeRequest || $emailChangeRequest->verified_at) {
                return [
                    'status' => false,
                    'message' => __('errors.email_change_link_invalid'),
                    'code' => 400,
                ];
            }

            // Check whether the request has expired.
            if ($emailChangeRequest->expires_at->isPast()) {
                return [
                    'status' => false,
                    'message' => __('errors.email_change_link_expired'),
                    'code' => 410,
                ];
            }

            // Lock the user record before updating the email.
            $user = User::whereKey($emailChangeRequest->user_id)
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('errors.email_change_link_invalid'),
                    'code' => 400,
                ];
            }

            // Prevent assigning an email that now belongs to another user.
            if (
                $this->userRepository->emailExists(
                    $emailChangeRequest->new_email,
                    $user->id
                )
            ) {
                return [
                    'status' => false,
                    'message' => __('validation.unique', [
                        'attribute' => 'email',
                    ]),
                    'code' => 409,
                ];
            }

            // Prevent unnecessary changes if the email is already the same.
            if (strtolower($user->email) === strtolower($emailChangeRequest->new_email)) {
                return [
                    'status' => false,
                    'message' => __('errors.email_change_link_invalid'),
                    'code' => 400,
                ];
            }

            // Update the user's email and verification timestamp.
            $user->forceFill([
                'email' => $emailChangeRequest->new_email,
                'email_verified_at' => now(),
            ])->save();

            // Mark the request as verified so the token cannot be reused.
            $emailChangeRequest->update([
                'verified_at' => now(),
            ]);

            return [
                'status' => true,
                'message' => __('messages.email_changed_successfully'),
                'data' => $user->fresh(),
                'code' => 200,
            ];
        });
    }


}
