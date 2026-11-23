<?php

namespace App\Http\Services\Profile;

use App\Exceptions\InvalidMobileNumberException;
use App\Exceptions\User\LimitChangeUsernameException;
use App\Exceptions\WrongCurrentPasswordException;
use App\Http\Services\OTP\OTPService;
use App\Http\Services\Public\MediaStorageService;
use App\Repositories\Contracts\User\OTPRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class ProfileService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected MediaStorageService $mediaStorageService,
        protected OTPRepositoryInterface $oTPRepository,
        protected OTPService $oTPService
    ) {
    }

    public function getBasicInfo()
    {
        $user = $this->userRepository->showUser(auth()->user());
        return $user;
    }
    public function updateProfile(array $data)
    {
        $user = auth()->user();
        $data['profile_photo_path'] = $this->mediaStorageService->updateImageIfExists(
            $data['profile_photo_path'],
            $user->profile_photo_path,
            "images/profiles/users/{$user->id}",
            null
        );
        $this->userRepository->update($user, $data);
        return $user->fresh();
    }
    public function aboutMe(array $data)
    {
        $user = auth()->user();
        $this->userRepository->update($user, $data);
        return $user->fresh();
    }
    public function changePassword(array $data)
    {
        $user = auth()->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new WrongCurrentPasswordException();
        }
        $this->userRepository->update($user, [
            'password' => Hash::make($data['new_password'])
        ]);
        return $user->fresh();
    }

    public function changeMobile(array $data)
    {
        $user = auth()->user();

        // Normalize the new mobile number.
        $mobile = normalizeMobile($data['id']);

        if (!$mobile) {
            throw new InvalidMobileNumberException();
        }

        // Prevent requesting the current mobile number.
        if ($user->mobile === $mobile) {
            throw new InvalidMobileNumberException();
        }

        // Check whether the mobile number belongs to another user.
        $oldUser = $this->userRepository->findByMobile($mobile);

        if ($oldUser) {
            throw new InvalidMobileNumberException();
        }

        // Check OTP resend cooldown.
        $oldOTP = $this->oTPService->checkOldOtp($mobile, 0);

        if ($oldOTP) {
            return [
                'status' => false,
                'data' => null,
                'message' => __('errors.wait_for_otp',['second' => $oldOTP->timer]),
                'code' => 429,
            ];
        }

        // Create and send OTP.
        $type = 0;
        $otp = $this->oTPService->createOtp($mobile, $type, $user->id);

        $this->oTPService->sendSms($mobile, $otp->otp_code);

        return [
            'status' => true,
            'data' => [
                'token' => $otp->token,
                'meta' => [
                    'next_step' => 'redirect_to_/confirm_otp',
                ],
            ],
            'message' => __('messages.confirm_mobile_with_otp'),
            'code' => 200,
        ];
    }

    public function confirmMobile(string $token, array $data)
    {
        $user = auth()->user();

        return DB::transaction(function () use ($token, $data, $user) {

            $otp = $this->oTPRepository->findByUserTokenForUpdate(
                $token,
                $user->id
            );

            if (!$otp) {
                return [
                    'status' => false,
                    'message' => __('errors.link_is_invalid_or_expired'),
                    'code' => 401,
                ];
            }

            if (!hash_equals((string) $otp->otp_code, (string) $data['otp'])) {
                return [
                    'status' => false,
                    'message' => __('errors.otp_code_is_invalid'),
                    'code' => 401,
                ];
            }

            $mobile = normalizeMobile($otp->login_id);

            if (!$mobile) {
                return [
                    'status' => false,
                    'message' => __('errors.mobile_is_invalid'),
                    'code' => 422,
                ];
            }

            // Ensure the mobile number is still available.
            $existingUser = $this->userRepository->findByMobile($mobile);

            if ($existingUser && $existingUser->id !== $user->id) {
                return [
                    'status' => false,
                    'message' => __('errors.mobile_already_exists'),
                    'code' => 409,
                ];
            }

            // Mark OTP as used.
            $this->oTPRepository->update($otp, [
                'used' => 1,
            ]);

            // Update the user's mobile number.
            $this->userRepository->update($user, [
                'mobile' => $mobile,
                'mobile_verified_at' => now(),
            ]);

            return [
                'status' => true,
                'message' => __('messages.mobile_confirmed'),
                'data' => $user->fresh(),
                'code' => 200,
            ];
        });
    }

    public function changeUsername(array $data)
    {
        $user = auth()->user();
        if ($user->username_change_count >= 2) {
            throw new LimitChangeUsernameException();
        }
        $this->userRepository->update($user, [
            'username' => $data['username'],
            'username_change_count' => $user->username_change_count + 1,
        ]);

        return $user->fresh();
    }

    public function changeLocale(string $locale)
    {
        $supportedLocales = config('app.supported_locales', ['en']);

        if (!in_array($locale, $supportedLocales, true)) {
            throw ValidationException::withMessages([
                'locale' => ['The selected language is not supported.'],
            ]);
        }

        $user = auth()->user();

        $user = $this->userRepository->updateLocale($user, $locale);

        // Apply the new locale to the current request.
        App::setLocale($locale);

        return $user;
    }

}