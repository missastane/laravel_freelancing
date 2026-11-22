<?php

namespace App\OpenApi\Requests\Market\Subscription;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubscriptionRequestSchema',
    type: 'object',
    required: [
        'name',
        'amount',
        'duration_days',
        'commission_rate',
        'max_target_per_month',
        'max_notification_per_month',
        'max_email_per_month',
        'max_sms_per_month',
        'max_view_details_per_month',
    ],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'پلن طلایی'),
        new OA\Property(property: 'amount', type: 'integer', example: 300000),
        new OA\Property(property: 'duration_days', type: 'integer', example: 30),
        new OA\Property(property: 'commission_rate', type: 'integer', example: 10),
        new OA\Property(property: 'max_target_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_notification_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_email_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_sms_per_month', type: 'integer', example: 20),
        new OA\Property(property: 'max_view_details_per_month', type: 'integer', example: 20),
        new OA\Property(
            property: 'features',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                required: [
                    'feature_key',
                    'feature_persian_key',
                    'feature_value',
                    'feature_value_type',
                    'is_limited',
                ],
                properties: [
                    new OA\Property(property: 'feature_key', type: 'string', example: 'vip-proposal'),
                    new OA\Property(property: 'feature_persian_key', type: 'string', example: 'پیشنهاد اختصاصی'),
                    new OA\Property(property: 'feature_value', type: 'string', example: '10'),
                    new OA\Property(property: 'feature_value_type', type: 'string', example: 'عدد'),
                    new OA\Property(property: 'is_limited', type: 'integer', enum: [1, 2], example: 1),
                ]
            )
        ),
    ]
)]
class SubscriptionRequestSchema
{
}
