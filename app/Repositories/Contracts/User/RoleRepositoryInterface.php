<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\Role;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function getRoles(int $perPage = 15): Paginator;
    public function getRoleOptions(): Collection;
    public function firstOrCreate(string $role);
    public function show(Role $role): Role;
    public function storeRole(array $data): Role;
    public function updateRole(Role $role, array $data): bool;
    public function deleteRole(Role $role);
    public function detachPermissions(Role $role);
    public function detachUsers(Role $role);
    public function syncPermissions(Role $role, array $permissions);
    public function findByName(string $name);

}