<?php

namespace App\Repositories\Contracts\User;

use App\Models\Market\Project;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends
  CreatableRepositoryInterface,
  ShowableRepositoryInterface,
  UpdatableRepositoryInterface,
  DeletableRepositoryInterface
{
  public function findById(int $id);
  public function findByEmail(string $email): User|null;
  public function findByMobile(string $mobile);
  public function getUsers(int $type,$message);
  public function getAdmins();
  public function searchUsers(int $type, string $search, $message);
  public function showUser(User $user);
  public function toggleActivation(User $user): string|null;
  public function getFreelancerWithSkills(array $skills): Collection|null;
  public function getproposedFreelancers(Project $project): Collection|null;
  public function updatePassword(User $user, string $password): User;
  public function verifyUser(User $user): User;
  public function assignRole(User $user, string|int|array|Role|Collection $roles);
  public function rolesSync(User $user, array $roles);
  public function permissionsSync(User $user, array $permissions);
  public function detachRoles(User $user);
  public function detachPermissions(User $user);
  public function deleteAdmin(User $admin);

}
