<?php

namespace App\Http\Services\User;

use App\Jobs\SendResetPasswordUrl;
use App\Models\User\User;
use App\Repositories\Contracts\User\PermissionRepositoryInterface;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminUserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PermissionRepositoryInterface $permissionRepository,
        protected RoleRepositoryInterface $roleRepository
    ) {

    }
    public function getAdmins($message)
    {
        return $this->userRepository->getUsers(2,$message);
    }

    public function options(): array
    {
        return [
            'roles' => $this->roleRepository->getRoleOptions(),
            'permissions' => $this->permissionRepository->getPermissionOptions()
        ];
    }
    public function searchAdmins(string $search, $message)
    {
        return $this->userRepository->searchUsers(2, $search,$message);
    }
    public function showAdmin(User $admin)
    {
        return $this->userRepository->showUser($admin);
    }
    public function storeNewAdmin(array $data)
    {
        return DB::transaction(function () use ($data) {
            $username = Str::random(16);
            $password = Str::random(24);

            $data['password'] = Hash::make($password);
            $data['username'] = $username;
            $data['user_type'] = 2;
            $data['active_role'] = 'admin';

            $admin = $this->userRepository->create($data);

            $this->userRepository->assignRole($admin,$admin->active_role);

            $token = Password::createToken($admin);
            SendResetPasswordUrl::dispatch($admin, $token);

            return $admin;
        });
    }

    public function toggleActivation(User $user): string|null
    {
        return $this->userRepository->toggleActivation($user);
    }
    public function update(User $user, array $data)
    {
        return $this->userRepository->update($user, $data);
    }
    public function delete(User $user)
    {
        return $this->userRepository->deleteAdmin($user);
    }
    public function syncRoles(User $admin, array $data): User
    {
        $adminRole = $this->roleRepository->findByName('admin');
        if(!$adminRole){
            $adminRole =$this->roleRepository->create(['name' => 'admin','guard_name' => 'api']);
        }
        $adminRoleId = $adminRole->id;
        array_push($data,$adminRoleId);
        $this->userRepository->rolesSync($admin,$data);
        return $admin;
    }
    public function permissionsStore(User $admin, array $data): User
    {
        $this->userRepository->permissionsSync($admin,$data);
        return $admin;
    }

    public function syncDepartments(User $user, array $data)
    {
        return $this->userRepository->syncDepartments($user,$data);
    }
}