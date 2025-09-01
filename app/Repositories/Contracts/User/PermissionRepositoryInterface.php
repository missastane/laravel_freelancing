<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\Permission;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function getPermissionOptions(): Collection;
    public function storePermission(array $data): Permission;
    public function updatePermission(Permission $permission, array $data): bool;
    public function detachRoles(Permission $permission);
    public function detachUsers(Permission $permission);
    public function deletePermission(Permission $permission);
    public function syncPermissionToRoles(Permission $permission, array $data);
}