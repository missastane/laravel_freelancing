<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrorResponse',
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
            example: 'The given data was invalid.'
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Field-specific validation errors.',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]
        ),
        new OA\Property(
            property: 'error_list',
            type: 'array',
            description: 'Flat list of all validation error messages.',
            items: new OA\Items(type: 'string'),
            example: [
                'The email field is required.',
                'The password field is required.'
            ]
        )
    ]
)]
class ValidationErrorResponse
{
}
