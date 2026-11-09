<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\ResendEmailVerificationRequest;
use App\Http\Services\User\UserService;

use App\Traits\ApiResponseTrait;
use Exception;
use OpenApi\Attributes as OA;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected UserService $userService)
    {
    }
    #[OA\Post(
        path: "/api/email/verification-notification",
        summary: "Resend email verification link",
        description: "Resends the email verification link to the user if their email is not already verified.",
        operationId: "resendVerificationEmail",
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
                description: "Verification link sent successfully",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Email already verified",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
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
    public function resendVerificationEmail(ResendEmailVerificationRequest $request)
    {
        try {
            $this->userService->resendVerificationEmail($request->all());

            return $this->success(
                null,
                __('messages.sent_email_verification_link')
            );
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Get(
        path: "/api/auth/email/verify",
        summary: "Check email verification status",
        description: "Checks whether the authenticated user's email is verified or not.",
        operationId: "checkVerificationStatus",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Email is already verified",
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
            new OA\Response(
                response: 403,
                description: "Email is not verified",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function checkVerificationStatus(Request $request)
    {
        $result = $this->userService->checkVerificationStatus($request->user());
        if (!$result['status']) {
            return $this->error($result['message'], $result['code']);
        }
        return $this->success(null, $result['message'], $result['code']);
    }

    #[OA\Get(
        path: "/api/auth/email/verify/{id}/{hash}",
        summary: "Verify user email",
        description: "Verifies the user's email address using the ID and hash provided in the verification link.",
        operationId: "verifyEmail",
        tags: ["Authentication"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "User ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "hash",
                description: "Hashed email token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "c4ca4238a0b923820dcc509a6f75849b")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Email verified successfully",
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
                response: 400,
                description: "Email already verified",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "Invalid verification link",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
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
    public function verifyEmail(Request $request, $id, $hash)
    {
        $result = $this->userService->verifyEmail(
            $request->all(),
            $id,
            $hash
        );

        $frontendUrl = config('services.frontend_url');

        if ($result['status']) {
            return redirect()->away(
                $frontendUrl . '/auth/email-verified?status=success'
            );
        }

        $status = $result['code'] === 400
            ? 'already_verified'
            : 'failed';

        return redirect()->away(
            $frontendUrl . '/auth/email-verified?status=' . $status
        );
    }
}
