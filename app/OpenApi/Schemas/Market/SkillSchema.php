<?php

namespace App\OpenApi\Schemas\Market;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SkillSchema',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'persian_title', type: 'string', example: 'ورد'),
        new OA\Property(property: 'original_title', type: 'string', example: 'word'),
        new OA\Property(
            property: 'deleted_at',
            type: 'string',
            format: 'date-time',
            description: 'Delete datetime',
            nullable: true,
            example: '2025-02-22T14:30:00Z'
        ),
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
    ]
)]
class SkillSchema
{
}
