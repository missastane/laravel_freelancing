<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswaordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\User\UserService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class PasswordResetController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected UserService $userService)
    {
    }

    #[OA\Post(
        path: "/api/auth/forgot-password",
        summary: "Request password reset email",
        description: "Sends a password reset link to the given email address. Rate limiting is applied to prevent spam.",
        operationId: "forgotPassword",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "user@example.com"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password reset email sent successfully",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
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
                response: 429,
                description: "Too many requests",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            )
        ]
    )]
    public function forgotPassword(ForgotPasswaordRequest $request)
    {
        try {
            $result = $this->userService->forgotPassword(
                $request->validated()
            );

            if (!$result) {
                return $this->error(
                    __('errors.link_already_sent'),
                    429
                );
            }

            return $this->success(
                null,
                __('messages.password_recovery_link_sent')
            );
        } catch (Exception $e) {
            Log::error('Forgot password caused an error: ' . $e->getMessage());

            throw $e;
        }
    }

    #[OA\Post(
        path: "/api/auth/reset-password",
        summary: "Reset user password",
        description: "Resets the password using the provided token and new password.",
        operationId: "resetPassword",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token", "password", "password_confirmation"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "user@example.com"
                    ),
                    new OA\Property(
                        property: "token",
                        type: "string",
                        example: "a1b2c3d4e5f6g7h8"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "Ex@mpl8N0risk7"
                    ),
                    new OA\Property(
                        property: "password_confirmation",
                        type: "string",
                        format: "password",
                        example: "Ex@mpl8N0risk7"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password reset successfully",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid credentials or token",
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
                description: "Server error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            )
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->userService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        if ($result['status']) {
            return $this->success(null, $result['message']);
        }
        return $this->error($result['message'], $result['code'] ?? 400);
    }
}
