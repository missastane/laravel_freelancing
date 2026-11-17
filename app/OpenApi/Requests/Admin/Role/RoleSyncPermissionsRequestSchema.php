<?php

namespace App\OpenApi\Requests\Admin\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RoleSyncPermissionsRequestSchema',
    type: 'object',
    required: ['permissions'],
    properties: [
        new OA\Property(
            property: 'permissions',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3]
        ),
    ]
)]
class RoleSyncPermissionsRequestSchema
{
}
