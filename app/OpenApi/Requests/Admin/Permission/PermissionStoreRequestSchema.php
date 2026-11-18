<?php

namespace App\OpenApi\Requests\Admin\Permission;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PermissionStoreRequestSchema',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            description: 'Only English alphabet characters with (-, .)',
            pattern: '^[a-zA-Z]+([-.][a-zA-Z]+)*$',
            example: 'edit-comments'
        ),
    ]
)]
class PermissionStoreRequestSchema
{
}
