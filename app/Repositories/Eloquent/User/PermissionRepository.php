<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\Permission;
use App\Repositories\Contracts\User\PermissionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    public function getPermissions(int $perPage = 15, null|string $search): Paginator
    {
        return $this->model
            ->with('roles')
            ->search($search)
            ->withCount('roles')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function getPermissionOptions(): Collection
    {
        return $this->model->query()->select('id', 'name')->get();
    }

    public function show(Permission $role): Permission
    {
        return $this->showWithRelations($role, ['roles'])
            ->loadCount('roles');
    }

    public function storePermission(array $data): Permission
    {
        $name = $data['name'];
        return $this->create([
            'name' => $name,
            'guard_name' => 'api'
        ]);
    }
    public function updatePermission(Permission $permission, array $data): bool
    {
        $name = $data['name'];
        return $this->update($permission, ['name' => $name]);
    }

    public function detachRoles(Permission $permission)
    {
        return $permission->roles()->detach();
    }
    public function detachUsers(Permission $permission)
    {
        return $permission->users()->detach();
    }
    public function deletePermission(Permission $permission)
    {
        $this->detachUsers($permission);
        $this->detachRoles($permission);
        return $this->delete($permission);
    }
    public function syncPermissionToRoles(Permission $permission, array $data)
    {
        $permission = $this->showWithRelations($permission);
        return $permission->syncRoles($data);
    }

}