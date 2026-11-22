<?php

namespace App\OpenApi\Requests\Profile;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangePasswordRequestSchema',
    type: 'object',
    required: ['current_password', 'new_password', 'new_password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', minLength: 8, example: 'fL520khf56'),
        new OA\Property(property: 'new_password', type: 'string', minLength: 8, example: 'fL520khf56'),
        new OA\Property(property: 'new_password_confirmation', type: 'string', minLength: 8, example: 'fL520khf56'),
    ]
)]
class ChangePasswordRequestSchema
{
}
