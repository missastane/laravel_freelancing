<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AdminUser",
    title: "Admin User",
    description: "Admin user schema extending the common user schema.",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/User"),
        new OA\Schema(
            type: "object",
            properties: [
                new OA\Property(
                    property: "roles",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/RoleOption"
                    )
                ),
                new OA\Property(
                    property: "permissions",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/PermissionOption"
                    )
                ),
                new OA\Property(
                    property: "departments",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/DepartmentOption"
                    )
                ),
            ]
        ),
    ]
)]
class AdminUser
{
}