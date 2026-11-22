<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;

class AdminUserResource extends UserResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'departments' => DepartmentOptionResource::collection($this->whenLoaded('departments')),
        ]);
    }
}