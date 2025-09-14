<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\Role;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function getRoleOptions(): Collection;
    public function firstOrCreate(string $role);
    public function storeRole(array $data): Role;
    public function updateRole(Role $role, array $data): bool;
    public function deleteRole(Role $role);
    public function detachPermissions(Role $role);
    public function detachUsers(Role $role);
    public function syncPermissions(Role $role, array $permissions);
    public function findByName(string $name);

}