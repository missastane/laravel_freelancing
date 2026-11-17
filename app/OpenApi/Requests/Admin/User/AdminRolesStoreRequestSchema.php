<?php

namespace App\OpenApi\Requests\Admin\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminRolesStoreRequestSchema",
    type: "object",
    required: ["roles"],
    properties: [
        new OA\Property(
            property: "roles",
            type: "array",
            description: "List of role IDs to sync with the admin",
            items: new OA\Items(type: "integer", example: 2),
            example: [1, 2]
        ),
    ]
)]
class AdminRolesStoreRequestSchema
{
}
