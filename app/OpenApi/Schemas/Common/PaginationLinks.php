<?php

namespace App\OpenApi\Schemas\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PaginationLinks",
    title: "Pagination Links",
    description: "Pagination navigation links",
    type: "object",
    properties: [
        new OA\Property(property: "first", type: "string", example: "http://127.0.0.1:8000/api/resource?page=1"),
        new OA\Property(property: "last", type: "string", example: "http://127.0.0.1:8000/api/resource?page=1"),
        new OA\Property(property: "prev", type: "string", example: null, nullable: true),
        new OA\Property(property: "next", type: "string", example: null, nullable: true),
    ]
)]
class PaginationLinks
{
}
