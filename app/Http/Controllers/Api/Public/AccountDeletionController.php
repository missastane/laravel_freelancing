<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreAccountDeletionRequest;
use App\Http\Services\User\AccountDeletionService;
use App\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

class AccountDeletionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AccountDeletionService $accountDeletionService
    ) {
    }

    #[OA\Post(
        path: "/api/profile/account-deletion-requests",
        summary: "Request account deletion",
        description: "Authenticated users can submit an account deletion request. The request will be scheduled for processing after a 7-day cancellation period. Requests are rejected if the user has active orders, pending disputes, active projects, pending proposals, outstanding wallet balances, or pending payments. Admin accounts cannot use this endpoint.",
        tags: ["Customer-Profile"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["password"],
                properties: [
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "CurrentPassword123"
                    ),
                    new OA\Property(
                        property: "reason",
                        type: "string",
                        nullable: true,
                        maxLength: 1000,
                        example: "I no longer need this account."
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Account deletion request created successfully. The request will be processed after 7 days unless cancelled.",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),

                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/AccountDeletionRequest",
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "Admin accounts are not allowed to request account deletion.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 409,
                description: "Account deletion is blocked because of existing blockers, or a pending deletion request already exists.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Invalid current password or validation error.",
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
    public function store(StoreAccountDeletionRequest $request)
    {
        $result = $this->accountDeletionService->requestDeletion(
            $request->validated()
        );

        if (!$result['status']) {
            return $this->error(
                $result['message'],
                $result['code'] ?? 400
            );
        }

        return $this->success(
            $result['data'] ?? null,
            $result['message'],
            $result['code'] ?? 201
        );
    }

    #[OA\Patch(
        path: "/api/profile/account-deletion-requests/cancel",
        summary: "Cancel account deletion request",
        description: "Cancels the authenticated user's pending account deletion request during the 7-day grace period.",
        security: [["bearerAuth" => []]],
        tags: ["Account Deletion"],
        parameters: [
            new OA\Parameter(
                name: "X-Locale",
                in: "header",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["fa", "en"]),
                example: "en"
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Username is Updated Successfully",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),

                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/AccountDeletionRequest",
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 409,
                description: "No pending request exists or the cancellation period has expired"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
    public function cancel()
    {
        $result = $this->accountDeletionService->cancelDeletion();

        if (!$result['status']) {
            return $this->error(
                $result['message'],
                $result['code'] ?? 400
            );
        }

        return $this->success(
            $result['data'] ?? null,
            $result['message'],
            $result['code'] ?? 200
        );
    }
}
