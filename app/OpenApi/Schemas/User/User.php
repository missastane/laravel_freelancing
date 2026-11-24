<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    title: "User",
    description: "User details schema",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "username", type: "string", example: "ایمان"),
        new OA\Property(property: "email", type: "string", format: "email", example: "missastaneh@yahoo.com"),
        new OA\Property(property: "email_verified_at", description: "email verify datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z", nullable: true),
        new OA\Property(property: "mobile", type: "string", example: "09125478963", nullable: true),
        new OA\Property(property: "mobile_verified_at", description: "mobile verify datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z", nullable: true),
        new OA\Property(property: "first_name", type: "string", example: "ایمان", nullable: true),
        new OA\Property(property: "last_name", type: "string", example: "مدائنی", nullable: true),
        new OA\Property(property: "national_code", type: "string", example: "2732548965", nullable: true),
        new OA\Property(property: "locale", type: "string", example: "en"),
        new OA\Property(property: "gender", description: "1 => male, 2 => female", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "gender_type", description: "User gender: 'male' if 1, 'female' if 2", type: "string", example: "مذکر", nullable: true),
        new OA\Property(property: "birth_date", description: "birth datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z", nullable: true),
        new OA\Property(property: "avatar_photo", type: "string", format: "uri", example: "/path/image.jpg", nullable: true),
        new OA\Property(property: "activation", description: "Activation Value: 'active' if 1, 'inactive' if 2", type: "string", example: "فعال"),
        new OA\Property(property: "activation_date", description: "activation datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z", nullable: true),
        new OA\Property(property: "active_role", type: "string", example: "کارفرما"),
        new OA\Property(property: "about_me", type: "string", example: "رشته اصلی من ریاضی است", nullable: true),
        new OA\Property(
            property: "account_deletion_request",
            description: "Current pending account deletion request, if any.",
            nullable: true,
            properties: [
                new OA\Property(
                    property: "id",
                    type: "integer",
                    example: 12
                ),
                new OA\Property(
                    property: "status",
                    type: "integer",
                    description: "1 => pending",
                    example: 1
                ),
                new OA\Property(
                    property: "scheduled_for",
                    type: "string",
                    format: "date-time",
                    example: "2026-12-01T12:00:00Z"
                ),
                new OA\Property(
                    property: "created_at",
                    type: "string",
                    format: "date-time",
                    example: "2026-11-24T12:00:00Z"
                ),
            ],
            type: "object"
        ),
        new OA\Property(property: "created_at", description: "creation datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z"),
        new OA\Property(property: "updated_at", description: "update datetime", type: "string", format: "date-time", example: "2025-02-22T10:00:00Z"),
    ]
)]
class User
{
}
