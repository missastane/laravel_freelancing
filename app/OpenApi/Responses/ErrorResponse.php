<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    required: ['status', 'message', 'errors', 'error_list'],
    properties: [
        new OA\Property(
            property: 'status',
            type: 'boolean',
            example: false
        ),
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'An unexpected server error occurred.'
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Field-specific errors. Empty object when no field errors exist.',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: []
        ),
        new OA\Property(
            property: 'error_list',
            type: 'array',
            description: 'Flat list of all error messages.',
            items: new OA\Items(type: 'string'),
            example: ['An unexpected server error occurred.']
        )
    ]
)]
class ErrorResponse
{
}
