<?php

namespace App\Http\Services\Profile;

use App\Http\Services\Image\ImageService;
use App\Http\Services\OTP\OTPService;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Payment\UserBankAccount;
use App\Models\Payment\Wallet;
use App\Models\Payment\WalletTransaction;
use App\Models\Payment\Withdrawal;
use App\Models\User\Notification;
use App\Models\User\OTP;
use App\Models\User\User;
use App\Repositories\Contracts\User\OTPRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


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
        return $this->userRepository->update($user, $data);
    }
    public function aboutMe(array $data)
    {
        $user = auth()->user();
        return $this->userRepository->update($user, $data);
    }
    public function changePassword(array $data)
    {
        $user = auth()->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new Exception('کلمه عبور فعلی نادرست است', 403);
        }
        return $this->userRepository->update($user, [
            'password' => Hash::make($data['new_password'])
        ]);
    }
    public function changeMobile(array $data)
    {
        // get mobile number
        $user = auth()->user();
        $oldUser = $this->userRepository->findByMobile($data['id']);
        if (!empty($oldUser) || !normalizeMobile($data['id'])) {
            throw new Exception('شماره موبایل صحیح نیست یا از قبل وجود دارد', 422);
        }
        $oldOTP = $this->oTPService->checkOldOtp($data['id'], 0);

        if ($oldOTP) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'جهت ارسال مجدد کد تأیید لطفا ' . $oldOTP->timer . ' ثانیه دیگر منتظر بمانید',
                'code' => 429
            ];
        }
        // send sms
        $type = 0; //id is a mobile number;
        $otp = $this->oTPService->createOtp($data['id'], $type, $user->id);
        $this->oTPService->sendSms($data['id'], $otp->otp_code);
        return [
            'data' => [
                'token' => $otp->token,
                'meta' => [
                    'next_step' => 'redirect_to_/confirm_otp'
                ]
            ],
            'message' => 'جهت ویرایش موبایل یا ایمیل خود با وارد کردن کد تأیید 6 رقمی ارسال شده لطفا آن را تأیید نمایید'
        ];
    }
    public function confirmMobile($token, array $data)
    {
        $user = auth()->user();
        $otp = $this->oTPRepository->findByUserToken($token, $user->id);
        if (empty($otp)) {
            return [
                'status' => false,
                'data' => [
                    'token' => $token,
                    'meta' => [
                        'next_step' => 'redirect_back'
                    ]
                ],
                'message' => 'آدرس وارد شده معتبر نیست',
                'code' => 401
            ];
        }
        if ($otp->otp_code !== $data['otp']) {
            return [
                'status' => false,
                'data' => [
                    'token' => $token,
                    'meta' => [
                        'next_step' => 'redirect_back'
                    ]
                ],
                'message' => 'کد وارد شده معتبر نیست',
                'code' => 401
            ];
        }
        // if everything is ok:
        $this->oTPRepository->update($otp, ['used' => 1]);
        $this->userRepository->update($user, ['mobile_verified_at' => Carbon::now(), 'mobile' => normalizeMobile($otp->login_id)]);
        return [
            'status' => true,
            'message' => 'شماره موبایل شما با موفقیت تأیید شد',
            'data' => null,
            'code' => 200
        ];
    }
}