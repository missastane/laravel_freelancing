<?php

namespace App\OpenApi\Requests\Admin\Locale\City;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CityRequestSchema',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            pattern: '^[\x{0600}-\x{06FF} ]+$',
            description: 'Only Persian letters and spaces are allowed. Any other character results in a validation error.',
            example: 'رشت'
        ),
    ]
)]
class CityRequestSchema
{
}
