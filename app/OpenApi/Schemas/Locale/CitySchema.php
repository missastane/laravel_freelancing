<?php

namespace App\OpenApi\Schemas\Locale;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CitySchema',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'رشت'),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            description: 'Creation datetime',
            example: '2025-02-22T10:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            description: 'Update datetime',
            example: '2025-02-22T10:00:00Z'
        ),
        new OA\Property(
            property: 'province',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 3),
                new OA\Property(property: 'name', type: 'string', example: 'تهران'),
            ]
        ),
    ]
)]
class CitySchema
{
}
