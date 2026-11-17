<?php

namespace App\OpenApi\Requests\Admin\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminPermissionsStoreRequestSchema",
    type: "object",
    required: ["permissions"],
    properties: [
        new OA\Property(
            property: "permissions",
            type: "array",
            description: "List of permission IDs to sync with the admin",
            items: new OA\Items(type: "integer", example: 2),
            example: [1, 2]
        ),
    ]
)]
class AdminPermissionsStoreRequestSchema
{
}
