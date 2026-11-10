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
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected ProfileService $profileService)
    {
    }
    #[OA\Get(
        path: "/api/profile",
        summary: "Get Auth User details",
        description: "Returns the `Auth User` details",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully fetched Auth User details",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),

                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/User",
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Not Found",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function index()
    {
        $user = $this->profileService->getBasicInfo();
        return $this->success($user, __('messages.login_success', ['user' => $user->full_name]));
    }

    #[OA\Post(
        path: "/api/profile/update",
        summary: "Updates profile information",
        description: "This method updates `Profile Information` and saves it.",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["first_name", "last_name"],
                    properties: [
                        new OA\Property(
                            property: "first_name",
                            description: "This field can only contain Persian letters and space.",
                            type: "string",
                            example: "ایمان"
                        ),
                        new OA\Property(
                            property: "last_name",
                            description: "This field can only contain Persian letters and space.",
                            type: "string",
                            example: "مدائنی"
                        ),
                        new OA\Property(property: "national_code", type: "string", example: "2730154896"),
                        new OA\Property(property: "birth_date", type: "string", format: "date-time"),
                        new OA\Property(property: "profile_photo_path", type: "string", format: "binary", nullable: true),
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful Profile update",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                    ]

                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
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

    #[OA\Put(
        path: "/api/profile/about-me",
        summary: "Store or Update about-me details by customers",
        description: "In this method customers can Store or Update about-me details",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "about_me",
                        description: "This field can only contain Persian and English letters, numbers, and symbols (,،?!.).",
                        type: "string",
                        pattern: "^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$",
                        example: "این درباره من است"
                    ),
                    new OA\Property(
                        property: "gender",
                        description: "1 = male, 2 = female",
                        type: "integer",
                        enum: [1, 2],
                        example: 1
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful Profile Update",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            )
        ]
    )]
    public function addAboutMe(AboutMeRequest $request)
    {
        try {
            $this->profileService->aboutMe($request->all());
            return $this->success(null, 'اطلاعات شخصی شما با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Patch(
        path: "/api/profile/change-password",
        summary: "Update password by customers",
        description: "In this method customers can Update their password",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password", "new_password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", minimum: 8, example: "fL520khf56"),
                    new OA\Property(property: "new_password", type: "string", minimum: 8, example: "fL520khf56"),
                    new OA\Property(property: "new_password_confirmation", type: "string", minimum: 8, example: "fL520khf56"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful Password Update",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "Wrong current password",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
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

    #[OA\Patch(
        path: "/api/profile/change-mobile",
        summary: "Updates Mobile number",
        description: "This method updates `Mobile Number` and sends verification OTP.",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id"],
                properties: [
                    new OA\Property(
                        property: "id",
                        description: "Phone number format",
                        type: "string",
                        example: "09112356987"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully requested change of phone number",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "token", type: "string", example: "345dcugdbhcjolsfdtfsgh..."),
                                new OA\Property(
                                    property: "meta",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "next_step", type: "string", example: "redirect_to_/confirm_otp"),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
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

    #[OA\Put(
        path: "/api/profile/confirm-mobile/{token}",
        summary: "Confirm OTP to change Mobile",
        description: "This method updates mobile if OTP code is valid.",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "token",
                description: "Token from OTP record",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["otp"],
                properties: [
                    new OA\Property(property: "otp", type: "string", example: "123456"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Mobile is Updated Successfully",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "OTP is Invalid",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "کد وارد شده معتبر نیست"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "token", type: "string", example: "kdhbvjnjcxiygfuhdijh..."),
                                new OA\Property(
                                    property: "meta",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "next_step", type: "string", example: "redirect_back"),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
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

    #[OA\Patch(
        path: "/api/profile/change-username",
        summary: "Change auth user's username",
        description: "In this method auth users can change their username up to 2 times.",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "12v34Kf56"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Username is Updated Successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "نام کاربری شما با موفقیت بروزرسانی شد"),
                        new OA\Property(property: "data", type: "object", nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired token",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "Limit Exceeded",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
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
