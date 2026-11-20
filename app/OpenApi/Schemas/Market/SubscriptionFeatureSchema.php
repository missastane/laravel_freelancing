<?php

namespace App\OpenApi\Schemas\Market;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubscriptionFeatureSchema',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'feature_key', type: 'string', example: 'vip-proposal'),
        new OA\Property(property: 'feature_persian_key', type: 'string', example: 'پیشنهاد اختصاصی'),
        new OA\Property(property: 'feature_value', type: 'string', example: '10'),
        new OA\Property(property: 'feature_value_type', type: 'string', example: 'عدد'),
        new OA\Property(
            property: 'is_limited',
            type: 'boolean',
            description: 'Whether the feature is limited or not.',
            example: true
        ),
    ]
)]
class SubscriptionFeatureSchema
{
}
