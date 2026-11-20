<?php

namespace App\OpenApi\Schemas\Market;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubscriptionSchema',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'پلن نقره ای'),
        new OA\Property(property: 'amount', type: 'integer', example: 300000),
        new OA\Property(property: 'duration_days', type: 'integer', example: 30),
        new OA\Property(property: 'commission_rate', type: 'integer', example: 30),
        new OA\Property(property: 'max_target_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_notification_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_email_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_sms_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_view_details_per_month', type: 'integer', example: 20),
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
            property: 'features',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/SubscriptionFeatureSchema')
        ),
    ]
)]
class SubscriptionSchema
{
}
