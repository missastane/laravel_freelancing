<?php

namespace App\OpenApi\Requests\Admin\User\AdminUser;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminUpdateRequestSchema",
    type: "object",
    required: ["first_name", "last_name", "activation"],
    properties: [
        new OA\Property(
            property: "first_name",
            type: "string",
            pattern: "^[a-zA-Z\u0600-\u06FF0-9 ]+$",
            description: "Persian and English letters, digits, and spaces only.",
            example: "ایمان"
        ),
        new OA\Property(
            property: "last_name",
            type: "string",
            pattern: "^[a-zA-Z\u0600-\u06FF0-9 ]+$",
            description: "Persian and English letters, digits, and spaces only.",
            example: "مدائنی"
        ),
        new OA\Property(
            property: "activation",
            type: "integer",
            enum: [1, 2],
            description: "1 = active, 2 = inactive",
            example: 1
        ),
        new OA\Property(
            property: "_method",
            type: "string",
            description: "Method spoofing for multipart/form-data requests in Laravel",
            example: "PUT"
        ),
    ]
)]
class AdminUpdateRequestSchema
{
}
