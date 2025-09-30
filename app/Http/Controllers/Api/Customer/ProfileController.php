<?php

namespace App\Http\Controllers\Api\Customer;

use App\Exceptions\InvalidMobileNumberException;
use App\Exceptions\User\LimitChangeUsernameException;
use App\Exceptions\WrongCurrentPasswordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\AboutMeRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\ChangerUsernameRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Services\Profile\ProfileService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected ProfileService $profileService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get Auth User details",
     *     description="Returns the `Auth User` details",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Auth User details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/User"
     *             )
     *         )
     *   ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function index()
    {
        return $this->success($this->profileService->getBasicInfo());
    }

    /**
     * @OA\Post(
     *     path="/api/profile/update",
     *     summary="Updates profile information",
     *     description="This method updates `Profile Information` and save it.",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="first_name", type="string", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="ایمان"),
     *             @OA\Property(property="last_name", type="string", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="مدائنی"),
     *             @OA\Property(property="national_code", type="string", example="2730154896"),
     *             @OA\Property(property="birth_date", type="datetime"),
     *             @OA\Property(property="profile_photo_path", type="string", format="binary", nullable="true"),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Profile update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پروفایل با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *  )
     * )
     */
    public function updateBasicInfo(UpdateProfileRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->profileService->updateProfile($inputs);
            return $this->success(null, 'اطلاعات پروفایل با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Put(
     *     path="/api/profile/about-me",
     *     summary="Store or Update about-me details by customers",
     *     description="In this method customers can Store or Update about-me details",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="about_me", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="gender", type="integer", enum={1, 2}, description="1 = male, 2 = female", example=1),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Profile Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="اطلاعات شخصی شما با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function addAboutMe(AboutMeRequest $request)
    {
        try {
            $this->profileService->aboutMe($request->all());
            return $this->success(null, 'اطلاعات شخصی شما با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/profile/change-password",
     *     summary="Update password by customers",
     *     description="In this method customers can Update their password",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="current_password", type="string", minimum="8", example="fL520khf56"),
     *             @OA\Property(property="new_password", type="string", minimum="8", example="fL520khf56"),
     *             @OA\Property(property="new_password_confirmation", type="string", minimum="8", example="fL520khf56"),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Password Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="کلمه عبور با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="ٌWrong Current Password",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کلمه عبور فعلی نادرست است")
     *     )),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function updatePassword(ChangePasswordRequest $request)
    {
        try {
            $this->profileService->changePassword($request->all());
            return $this->success(null, 'کلمه عبور با موفقیت بروزرسانی شد');
        } catch (WrongCurrentPasswordException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/profile/change-mobile",
     *     summary="Updates Mobile number",
     *     description="This method updates `Mobile Number` and save it.",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="id", type="string", description="This field can only contain phone number. Any other characters will result in a validation error.", example="09112356987"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfull change phone number",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="جهت ویرایش موبایل یا ایمیل خود با وارد کردن کد تأیید 6 رقمی ارسال شده لطفا آن را تأیید نمایید"),
     *             @OA\Property(property="data", type="array", 
     *                  @OA\Items(
     *                    @OA\Property(property="token", type="string", example="345dcugdbhcjolsfdtfsgh..."),
     *                    @OA\Property(property="meta", type="array", 
     *                       @OA\Items(
     *                          @OA\Property(property="next_step", type="string", example="redirect_to_/confirm_otp"),
     *                         ),
     *                       )
     *                   )
     *                )
     *            )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *  )
     * )
     */
    public function updateMobile(Request $request)
    {
        try {
            $inputs = $request->all();
            $result = $this->profileService->changeMobile($inputs);
            return $this->success($result['data'], $result['message'], 200);
        } catch (InvalidMobileNumberException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Put(
     *     path="/api/profile/confirm-mobile/{token}",
     *     summary="Confirm OTP to change Mobile",
     *     description="This method Update Mobile if OTP code is valid. For testing this method, You must test `path:/api/admin/profile/change-mobile` first",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="token from otp record",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mobile is Updated Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="شماره موبایل شما با موفقیت تأیید شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="OTP is Invalid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="کد وارد شده معتبر نیست"),
     *             @OA\Property(property="data", type="array",
     *                @OA\Items(
     *                 @OA\Property(property="token", type="string", example="kdhbvjnjcxiygfuhdijh..."),
     *                 @OA\Property(property="meta", type="array",
     *                     @OA\Items(
     *                        @OA\Property(property="next_step", type="string", example="redirect_back")
     *                        )
     *                     )
     *                  )
     *               )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal servr error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function mobileConfirm($token, Request $request)
    {
        try {
            $inputs = $request->all();
            $result = $this->profileService->confirmMobile($token, $inputs);
            if ($result['status']) {
                return $this->success($result['data'], $result['message'], $result['code']);
            } else {
                return response()->json([
                    'status' => $result['status'],
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], $result['code']);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/profile/change-username",
     *     summary="Change auth user's username",
     *     description="In This method auth users can change their username 2 time",
     *     tags={"Customer-Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username"},
     *             @OA\Property(property="username", type="string", example="12v34Kf56")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Username is Updated Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="نام کاربری شما با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="تغییر نام کاربری بیش از ۲ بار مجاز نیست")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal servr error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function changeUsername(ChangerUsernameRequest $request)
    {
        try {
            $this->profileService->changeUsername($request->all());
            return $this->success(null, 'نام کاربری شما با موفقیت بروزرسانی شد');
        } catch (LimitChangeUsernameException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
