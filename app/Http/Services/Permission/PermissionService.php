<?php

namespace App\Http\Services\Permission;

use App\Models\User\Permission;
use App\Repositories\Contracts\User\PermissionRepositoryInterface;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class PermissionService
{
    public function __construct(protected PermissionRepositoryInterface $permissionRepository)
    {
    }

    public function getPermissions(int $perPage = 15, string $search = null): Paginator
    {
        return $this->permissionRepository->getPermissions($perPage, $search);
    }

    public function showPermission(Permission $permission): Permission
    {
        return $this->permissionRepository->show($permission);
    }

    public function storePermission(array $data): Permission
    {
        return $this->permissionRepository->storePermission($data);
    }
    public function updatePermission(Permission $permission, array $data): bool
    {
        return $this->permissionRepository->updatePermission($permission, $data);
    }
    public function syncPermissionToRoles(Permission $permission, array $data)
    {
        return $this->permissionRepository->syncPermissionToRoles($permission, $data['roles']);
    }

    public function deletePermission(Permission $permission)
    {
        return $this->permissionRepository->deletePermission($permission);
    }
}