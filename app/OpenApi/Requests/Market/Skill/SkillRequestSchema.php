<?php

namespace App\OpenApi\Requests\Market\Skill;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SkillRequestSchema',
    type: 'object',
    required: ['persian_title', 'original_title'],
    properties: [
        new OA\Property(
            property: 'persian_title',
            type: 'string',
            description: 'Only Persian letters, numbers and spaces are allowed. Any other character results in a validation error.',
            example: 'ترجمه'
        ),
        new OA\Property(
            property: 'original_title',
            type: 'string',
            description: 'Only English letters, numbers and spaces are allowed. Any other character results in a validation error.',
            example: 'translate'
        ),
    ]
)]
class SkillRequestSchema
{
}
