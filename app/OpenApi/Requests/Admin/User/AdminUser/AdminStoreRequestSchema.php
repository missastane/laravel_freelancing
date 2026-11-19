<?php

namespace App\OpenApi\Requests\Admin\User\AdminUser;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminStoreRequestSchema',
    type: 'object',
    required: ['first_name', 'last_name', 'email', 'mobile'],
    properties: [
        new OA\Property(
            property: 'first_name',
            type: 'string',
            pattern: '^[a-zA-Z\u0600-\u06FF0-9 ]+$',
            description: 'Persian and English letters, digits, and spaces only.',
            example: 'ایمان'
        ),
        new OA\Property(
            property: 'last_name',
            type: 'string',
            pattern: '^[a-zA-Z\u0600-\u06FF0-9 ]+$',
            description: 'Persian and English letters, digits, and spaces only.',
            example: 'مدائنی'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'example@gmail.com'
        ),
        new OA\Property(
            property: 'mobile',
            type: 'string',
            example: '09123654789'
        ),
    ]
)]
class AdminStoreRequestSchema
{
}
