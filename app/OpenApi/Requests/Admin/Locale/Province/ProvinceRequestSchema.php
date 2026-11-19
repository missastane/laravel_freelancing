<?php

namespace App\OpenApi\Requests\Admin\Locale\Province;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProvinceRequestSchema',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            pattern: '^[\x{0600}-\x{06FF}\s]+$',
            description: 'Only Persian letters and spaces are allowed. Any other character results in a validation error.',
            example: 'بوشهر'
        ),
    ]
)]
class ProvinceRequestSchema
{
}
