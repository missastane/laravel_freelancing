<?php

namespace App\OpenApi\Requests\Market\Subscription;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateSubscriptionRequestSchema',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            description: 'This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.',
            example: 'پلن طلایی'
        ),
    ]
)]
class UpdateSubscriptionRequestSchema
{
}
