<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PermissionSchema',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'edit-comments'),
        new OA\Property(property: 'guard_name', type: 'string', example: 'api'),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2025-09-24T14:12:24.000000Z'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2025-09-24T14:12:24.000000Z'
        ),
          new OA\Property(
            property: "roles",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RoleWithPivotSchema")
        ),
        new OA\Property(
            property: "roles_count",
            type: "integer",
            example: 12
        ),
    ]
)]
class PermissionSchema
{
}
