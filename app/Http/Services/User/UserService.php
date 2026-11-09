<?php

namespace App\Http\Services\User;

use App\Jobs\ResetPasswordJob;
use App\Jobs\SendVerificationEmail;
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
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected RoleRepositoryInterface $roleRepository
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

        $token = JWTAuth::fromUser($user);

        return [
            'status' => true,
            'message' => __('messages.login_success', ['user' => $user->full_name]),
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
            'code' => 200,
        ];
    }

    public function logoutUser(): void
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        auth()->logout();
    }

    public function refresh(): string
    {
        try {
            return JWTAuth::refresh(true, true);
        } catch (TokenExpiredException $e) {
            throw new \RuntimeException(__('errors.token_expired'), 401);
        } catch (TokenBlacklistedException $e) {
            throw new \RuntimeException(__('errors.token_black_listed'), 401);
        } catch (Exception $e) {
            throw new \RuntimeException(__('errors.server_error'), 500);
        }
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
        $email = $data['email'];
        $key = "reset-password-throttle:{$email}";

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        RateLimiter::hit($key, 60 * 30);

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
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
}
