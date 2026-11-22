<?php

namespace App\OpenApi\Requests\Profile;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AboutMeRequestSchema',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'about_me',
            type: 'string',
            pattern: '^[a-zA-Z\x{0600}-\x{06FF}0-9\s\-,،?!.]+$',
            description: 'This field can only contain Persian and English letters and numbers, plus the symbols (, ، ? ! .). Any other characters will result in a validation error.',
            example: 'این تیکت منه'
        ),
        new OA\Property(
            property: 'gender',
            type: 'integer',
            enum: [1, 2],
            description: '1 = male, 2 = female',
            example: 1
        ),
    ]
)]
class AboutMeRequestSchema
{
}
