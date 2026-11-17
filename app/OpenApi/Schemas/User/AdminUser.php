<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminUser",
    title: "Admin User",
    description: "Admin User schema",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 13),
        new OA\Property(property: "username", type: "string", example: "8uIZLvdTYrtMDWcJ"),
        new OA\Property(property: "email", type: "string", format: "email", example: "missastan@gmail.com"),
        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", example: "2025-07-09T07:53:16.000000Z", nullable: true),
        new OA\Property(property: "mobile", type: "string", example: null, nullable: true),
        new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", example: null, nullable: true),
        new OA\Property(property: "first_name", type: "string", example: "ایمان", nullable: true),
        new OA\Property(property: "last_name", type: "string", example: "مدائنی", nullable: true),
        new OA\Property(property: "gender", type: "string", example: null, nullable: true),
        new OA\Property(property: "birth_date", type: "string", format: "date-time", example: null, nullable: true),
        new OA\Property(property: "avatar_photo", type: "string", format: "uri", example: null, nullable: true),
        new OA\Property(property: "activation", type: "string", example: "کاربر فعال"),
        new OA\Property(property: "active_role", type: "string", example: "employer"),
        new OA\Property(property: "about_me", type: "string", example: null, nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2025-07-09T07:51:06.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2025-07-09T07:53:17.000000Z"),
        new OA\Property(
            property: "roles",
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 2),
                    new OA\Property(property: "name", type: "string", example: "employer"),
                ]
            )
        ),
        new OA\Property(
            property: "permissions",
            type: "array",
            items: new OA\Items(type: "string", example: "view-users")
        ),
    ]
)]
class AdminUser
{
}
