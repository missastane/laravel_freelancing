<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RoleSchema",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "admin"),
        new OA\Property(property: "guard_name", type: "string", example: "api"),
        new OA\Property(
            property: "permissions",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/PermissionSchema")
        ),
        new OA\Property(
            property: "permissions_count",
            type: "integer",
            example: 12
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class RoleSchema
{
}
