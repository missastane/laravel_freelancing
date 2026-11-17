<?php

namespace App\OpenApi\Schemas\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DepartmentOption',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'name', type: 'string', example: 'Financial department'),
        new OA\Property(property: 'status', type: 'integer', enum: [1, 2], description: '1 => active, 2 => deactive')
    ]
)]
class DepartmentOption
{
}
