<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Services\User\UserService;
use App\Traits\ApiResponseTrait;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Laravel_Freelancing API",
    version: "1.0.0",
    description: "API For Freelancing Projects",
    contact: new OA\Contact(
        email: "missastaneh@gmail.com"
    ),
    license: new OA\License(
        name: "Missastaneh",
        url: "https://opensource.org/licenses/MIT"
    )
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer"
)]
class AuthController extends Controller
{
    use ApiResponseTrait;


    public function __construct(protected UserService $userService)
    {
    }

    #[OA\Post(
        path: "/api/auth/register",
        summary: "Register a new user",
        description: "This method registers a new user and sends an approval link to verify the email.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/RegisterRequest"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Registration successful and verification link sent",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function register(AuthRequest $request)
    {
        try {
            $user = $this->userService->registerUser($request->all());
            return $this->success(null, 'با تشکر از ثبت نام شما. لینک تأیید ایمیل به آدرس ایمیل وارد شده ارسال گردید. لطفا ابتدا ایمیل خود را تأیید فرمایید', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/api/auth/login",
        summary: "User Login",
        description: "Authenticate a user and return a JWT token if credentials are valid and email is verified.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "missastaneh@gmail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "r@mZ4Ob00r"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "access_token",
                                            type: "string",
                                            example: "eyJ0eXAiOiJK..."
                                        ),
                                        new OA\Property(
                                            property: "token_type",
                                            type: "string",
                                            example: "bearer"
                                        ),
                                        new OA\Property(
                                            property: "expires_in",
                                            type: "integer",
                                            example: 3600
                                        ),
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
        ]
    )]
    public function login(AuthRequest $request)
    {
        try {
            $result = $this->userService->loginUser($request->only(['email', 'password']));
            if (!$result['status']) {
                return $this->error($result['message'], $result['code']);
            }
            return $this->success($result['data'], $result['message'], $result['code']);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/api/auth/logout",
        summary: "User Logout",
        description: "Invalidate the current JWT token and log the user out.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful logout",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function logout()
    {
        try {
            $this->userService->logoutUser();
            return $this->success(null, 'کاربر با موفقیت از حساب کاربری خود خارج شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Post(
        path: "/api/auth/refresh",
        summary: "Refresh JWT Token",
        description: "Refresh the expired JWT token and return a new token.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refreshed successfully",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "access_token",
                                            type: "string",
                                            example: "eyJ0eXAiOiJK..."
                                        ),
                                        new OA\Property(
                                            property: "token_type",
                                            type: "string",
                                            example: "bearer"
                                        ),
                                        new OA\Property(
                                            property: "expires_in",
                                            type: "integer",
                                            example: 3600
                                        ),
                                    ]
                                )
                            ]
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
                response: 500,
                description: "Unexpected server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function refresh()
    {
        try {
            $newToken = $this->userService->refresh();
            return $this->success([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (\RuntimeException $e) {
            return $this->error();
        }
    }

}
