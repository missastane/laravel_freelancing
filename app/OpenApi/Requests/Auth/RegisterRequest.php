<?php

namespace App\OpenApi\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    type: 'object',
    required: ['username', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(
            property: 'username',
            type: 'string',
            example: 'john_doe'
        ),
        new OA\Property(
            property: 'role',
            type: 'integer',
            enum: [1, 2],
            example: 1
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'example@example.com'
        ),
        new OA\Property(
            property: 'password',
            type: 'string',
            format: 'password',
            example: 'r@mZ4Ob00r'
        ),
        new OA\Property(
            property: 'password_confirmation',
            type: 'string',
            format: 'password',
            example: 'r@mZ4Ob00r'
        ),
    ]
)]
class RegisterRequest
{
}
