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
            example: 'Operation completed successfully.'
        ),
        new OA\Property(
            property: 'data',
            nullable: true,
            description: 'Response data. Its structure depends on the endpoint.'
        )
    ]
)]
class SuccessResponse
{
}
