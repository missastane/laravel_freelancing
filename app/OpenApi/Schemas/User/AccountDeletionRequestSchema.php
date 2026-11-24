<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AccountDeletionRequest",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "user_id",
            type: "integer",
            example: 10
        ),
        new OA\Property(
            property: "status",
            type: "integer",
            description: "1 => pending, 2 => cancelled, 3 => completed, 4 => failed",
            enum: [1, 2, 3, 4],
            example: 2
        ),
        new OA\Property(
            property: "reason",
            type: "string",
            nullable: true,
            example: "I no longer need this account."
        ),
        new OA\Property(
            property: "scheduled_for",
            type: "string",
            format: "date-time"
        ),
        new OA\Property(
            property: "processed_at",
            type: "string",
            format: "date-time",
            nullable: true
        ),
        new OA\Property(
            property: "failure_reason",
            type: "string",
            nullable: true
        ),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time"
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time"
        ),
    ]
)]
class AccountDeletionRequestSchema
{
}