<?php

namespace App\Http\Services\Auth;

use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private const SETUP_EXPIRATION_MINUTES = 10;

    public function __construct(
        protected Google2FA $google2fa
    ) {
    }

    /**
     * Start two-factor authentication setup.
     */
    public function startSetup(User $user): array
    {
        $twoFactorAuth = $user->twoFactorAuth;

        if ($twoFactorAuth?->isEnabled()) {
            return [
                'status' => false,
                'message' => __('errors.two_factor_already_enabled'),
                'code' => 409,
            ];
        }

        $secret = $this->google2fa->generateSecretKey();

        $expiresAt = now()->addMinutes(
            self::SETUP_EXPIRATION_MINUTES
        );

        $user->twoFactorAuth()->updateOrCreate(
            [],
            [
                'secret' => $secret,
                'confirmed_at' => null,
                'setup_expires_at' => $expiresAt,
            ]
        );

        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'status' => true,
            'message' => __('messages.two_factor_setup_started'),
            'code' => 200,
            'data' => [
                'secret' => $secret,
                'otpauth_url' => $otpauthUrl,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ];
    }

    public function confirmSetup(User $user, string $code): array
    {
        return DB::transaction(function () use ($user, $code) {
            $twoFactorAuth = $user->twoFactorAuth()
                ->lockForUpdate()
                ->first();

            if (!$twoFactorAuth || $twoFactorAuth->confirmed_at !== null) {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_setup_invalid'),
                    'code' => 400,
                ];
            }

            if (
                !$twoFactorAuth->setup_expires_at ||
                $twoFactorAuth->setup_expires_at->isPast()
            ) {
                $twoFactorAuth->delete();

                return [
                    'status' => false,
                    'message' => __('errors.two_factor_setup_expired'),
                    'code' => 410,
                ];
            }

            $timestamp = $this->google2fa->verifyKeyNewer(
                $twoFactorAuth->secret,
                $code,
                $twoFactorAuth->last_used_at ?? 0
            );

            if ($timestamp === false) {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_code_invalid'),
                    'code' => 422,
                ];
            }

            $recoveryCodes = [];

            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = strtoupper(bin2hex(random_bytes(5)));
            }

            $twoFactorAuth->update([
                'confirmed_at' => now(),
                'setup_expires_at' => null,
                'last_used_at' => $timestamp,
            ]);

            foreach ($recoveryCodes as $recoveryCode) {
                $user->twoFactorRecoveryCodes()->create([
                    'code_hash' => Hash::make($recoveryCode),
                ]);
            }

            return [
                'status' => true,
                'message' => __('messages.two_factor_enabled'),
                'code' => 200,
                'data' => [
                    'recovery_codes' => $recoveryCodes,
                ],
            ];
        });
    }

    public function disable(
        User $user,
        string $password,
        string $code
    ): array {
        return DB::transaction(function () use ($user, $password, $code) {
            // Verify the user's current password.
            if (!Hash::check($password, $user->password)) {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_disable_password_invalid'),
                    'code' => 422,
                ];
            }

            // Lock the two-factor configuration.
            $twoFactorAuth = $user->twoFactorAuth()
                ->lockForUpdate()
                ->first();

            if (!$twoFactorAuth || !$twoFactorAuth->isEnabled()) {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_not_enabled'),
                    'code' => 409,
                ];
            }

            // First, try to verify the submitted code as a TOTP code.
            $timestamp = $this->google2fa->verifyKeyNewer(
                $twoFactorAuth->secret,
                $code,
                $twoFactorAuth->last_used_at ?? 0
            );

            $isValidCode = $timestamp !== false;

            // If it is not a valid TOTP code, check recovery codes.
            if (!$isValidCode) {
                $recoveryCodes = $user->twoFactorRecoveryCodes()
                    ->whereNull('used_at')
                    ->lockForUpdate()
                    ->get();

                foreach ($recoveryCodes as $recoveryCode) {
                    if (Hash::check($code, $recoveryCode->code_hash)) {
                        $isValidCode = true;
                        break;
                    }
                }
            }

            if (!$isValidCode) {
                return [
                    'status' => false,
                    'message' => __('errors.two_factor_code_invalid'),
                    'code' => 422,
                ];
            }

            // Remove the two-factor configuration and all recovery codes.
            $twoFactorAuth->delete();

            $user->twoFactorRecoveryCodes()->delete();

            return [
                'status' => true,
                'message' => __('messages.two_factor_disabled'),
                'code' => 200,
            ];
        });
    }

}
