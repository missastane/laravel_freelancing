<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\Permission;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function getPermissions(int $perPage = 15, null|string $search): Paginator;
    public function getPermissionOptions(): Collection;
    public function show(Permission $role): Permission;
    public function storePermission(array $data): Permission;
    public function updatePermission(Permission $permission, array $data): bool;
    public function detachRoles(Permission $permission);
    public function detachUsers(Permission $permission);
    public function deletePermission(Permission $permission);
    public function syncPermissionToRoles(Permission $permission, array $data);
}