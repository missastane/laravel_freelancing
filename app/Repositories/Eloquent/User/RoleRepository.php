<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\Role;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function getRoleOptions(): Collection
    {
        return $this->model->query()->select('id', 'name')->get();
    }

    public function storeRole(array $data): Role
    {
        $name = $data['name'];
        return $this->create([
            'name' => $name,
            'guard_name' => 'api'
        ]);
    }
    public function updateRole(Role $role, array $data): bool
    {
        $name = $data['name'];
        return $this->update($role, ['name' => $name]);
    }

    public function detachPermissions(Role $role)
    {
        return $role->permissions()->detach();
    }
    public function detachUsers(Role $role)
    {
        return $role->users()->detach();
    }

    public function deleteRole(Role $role)
    {
        $this->detachPermissions($role);
        $this->detachUsers($role);
        return $this->delete($role);
    }

    public function syncPermissions(Role $role, array $permissions)
    {
        return $role->syncPermissions($permissions);
    }

    public function findByName(string $name)
    {
        return $this->model->where('name',$name)->first();
    }


}