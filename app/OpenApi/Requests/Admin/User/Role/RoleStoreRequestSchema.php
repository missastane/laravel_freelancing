<?php

namespace App\OpenApi\Requests\Admin\User\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RoleStoreRequestSchema',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            description: 'Unique name of the role',
            example: 'editor'
        ),
    ]
)]
class RoleStoreRequestSchema
{
}
