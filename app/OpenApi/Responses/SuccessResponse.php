<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SuccessResponse',
    type: 'object',
    required: ['status', 'message', 'data'],
    properties: [
        new OA\Property(
            property: 'status',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'message',
            type: 'string',
            nullable: true,
            example: null
        ),
        new OA\Property(
            property: 'data',
            nullable: true,
            description: 'Response data (can be object, array, or paginated items)'
        ),
        new OA\Property(
            property: 'links',
            ref: '#/components/schemas/PaginationLinks',
            nullable: true
        ),
        new OA\Property(
            property: 'meta',
            ref: '#/components/schemas/PaginationMeta',
            nullable: true
        ),
    ]
)]
class SuccessResponse
{
}
