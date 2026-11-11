<?php

namespace App\Http\Controllers;

use App\Http\Services\Profile\ProfileService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LocaleController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected ProfileService $profileService
    ) {
    }


    #[OA\Patch(
        path: "/api/profile/change-locale",
        summary: "Change authenticated user's locale",
        description: "Allows any authenticated user to change their preferred application language.",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["locale"],
                properties: [
                    new OA\Property(
                        property: "locale",
                        type: "string",
                        enum: ["fa", "en"],
                        example: "fa",
                        description: "The preferred application language."
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Language updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "زبان برنامه با موفقیت تغییر کرد"
                        ),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "locale",
                                    type: "string",
                                    example: "fa"
                                ),
                            ],
                            type: "object"
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
                description: "Validation error - Unsupported locale",
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
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => [
                'required',
                'string',
                'in:' . implode(',', config('app.supported_locales', ['en'])),
            ],
        ]);

        $user = $this->profileService->changeLocale($validated['locale']);

        return $this->success(
            [
                'locale' => $user->locale,
            ],
            __('messages.language_updated')
        );
    }

}
