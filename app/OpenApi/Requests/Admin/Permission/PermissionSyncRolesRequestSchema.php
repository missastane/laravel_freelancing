<?php

namespace App\OpenApi\Requests\Admin\Permission;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PermissionSyncRolesRequestSchema',
    type: 'object',
    required: ['roles'],
    properties: [
        new OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3]
        ),
    ]
)]
class PermissionSyncRolesRequestSchema
{
}
