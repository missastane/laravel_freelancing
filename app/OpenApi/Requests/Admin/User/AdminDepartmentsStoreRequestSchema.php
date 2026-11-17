<?php

namespace App\OpenApi\Requests\Admin\User;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminDepartmentsStoreRequestSchema",
    type: "object",
    required: ["departments"],
    properties: [
        new OA\Property(
            property: "departments",
            type: "array",
            description: "List of department IDs to sync with the admin",
            items: new OA\Items(type: "integer", example: 2),
            example: [1, 2]
        ),
    ]
)]
class AdminDepartmentsStoreRequestSchema
{
}
