<?php

namespace App\OpenApi\Schemas\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PaginationMeta",
    title: "Pagination Meta",
    description: "Pagination metadata details",
    type: "object",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "from", type: "integer", example: 1),
        new OA\Property(property: "last_page", type: "integer", example: 1),
        new OA\Property(
            property: "links",
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "url", type: "string", example: null, nullable: true),
                    new OA\Property(property: "label", type: "string", example: "&laquo; Previous"),
                    new OA\Property(property: "active", type: "boolean", example: false),
                ]
            )
        ),
        new OA\Property(property: "path", type: "string", example: "http://127.0.0.1:8000/api/resource"),
        new OA\Property(property: "per_page", type: "integer", example: 15),
        new OA\Property(property: "to", type: "integer", example: 4),
        new OA\Property(property: "total", type: "integer", example: 4),
    ]
)]
class PaginationMeta
{
}
