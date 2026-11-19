<?php

namespace App\Http\Services\Role;

use App\Models\User\Role;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $roleRepository)
    {
    }

    public function getRoles(int $perPage = 15, string $search = null): Paginator
    {
        return $this->roleRepository->getRoles($perPage, $search);
    }

    public function getOptions(): Collection
    {
        return $this->roleRepository->getRoleOptions();
    }

    public function showRole(Role $role): Role
    {
        return $this->roleRepository->show($role);
    }

    public function storeRole(array $data)
    {
        return $this->roleRepository->storeRole($data);
    }

    public function updateRole(Role $role, array $data): bool
    {
        return $this->roleRepository->updateRole($role, $data);
    }
    public function deleteRole(Role $role)
    {
        return $this->roleRepository->deleteRole($role);
    }
    public function syncPermissionsToRole(Role $role, array $data): Role
    {
        return $this->roleRepository->syncPermissions($role, $data['permissions']);
    }

}