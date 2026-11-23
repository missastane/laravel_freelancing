<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Market\Project;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
  use HasCreateTrait;
  use HasShowTrait;
  use HasUpdateTrait;
  use HasDeleteTrait;
  public function __construct(User $model)
  {
    parent::__construct($model);
  }

  protected function getUsersQuery(int $type)
  {
    return $this->model
      ->where('user_type', $type)
      ->with('roles', 'permissions')
      ->orderBy('id');
  }

  public function getAdmins()
  {
    return $this->model->whereHas('roles', function ($query) {
      $query->where('name', 'admin');
    })->get();
  }

  public function getUsers(int $type, null|string $search, int $perPage = 15)
  {
    $query = $this->getUsersQuery($type);

    if ($search) {
      $query->where(function ($query) use ($search) {
        $query->where('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('username', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%");
      });
    }

    $users = $query->paginate($perPage);

    return $users;
  }
  public function showUser(User $user)
  {
    $user = $this->showWithRelations($user, ['roles', 'permissions', 'departments']);
    return $user;
  }

  public function findByEmail(string $email): User
  {
    return $this->model->where('email', $email)->whereNotNull('email_verified_at')->first();
  }

  public function emailExists(string $email, ?int $exceptUserId = null): bool
  {
    return $this->model
      ->where('email', $email)
      ->when(
        $exceptUserId !== null,
        fn($query) => $query->where('id', '!=', $exceptUserId)
      )
      ->exists();
  }

  public function toggleActivation(User $user): string|null
  {
    $user->activation = $user->activation == 1 ? 2 : 1;
    if ($user->save()) {
      $message = $user->activation == 1
        ? __('messages.user_activated')
        : __('messages.user_deactivated');
      return $message;
    }
    return null;
  }

  public function findByMobile(string $mobile)
  {
    $user = $this->model->
      Where(
        'mobile',
        ltrim(preg_replace('/^(\+98|0)/', '', $mobile))
      )
      ->first();
    return $user;
  }
  public function getFreelancerWithSkills(array $skills): Collection|null
  {
    $freelancers = $this->model->with('skills')->where('active_role', 'freelancer')
      ->whereHas('skills', function ($query) use ($skills) {
        $query->whereIn('skill_id', $skills);
      })
      ->get();
    return $freelancers;
  }
  public function getproposedFreelancers(Project $project): Collection|null
  {
    return $this->model->where('active_role', 'freelancer')->with('proposals')
      ->whereHas('proposals', function ($query) use ($project) {
        $query->where('project_id', $project->id);
      })
      ->get();
  }

  public function findById(int $id)
  {
    return $this->model->findOrFail($id);
  }

  public function updatePassword(User $user, string $hashedPassword): User
  {
    $user->forceFill([
      'password' => $hashedPassword,
    ])->save();

    return $user;
  }

  public function updateLocale(User $user, string $locale): User
  {
    $user->forceFill([
      'locale' => $locale,
    ])->save();

    return $user;
  }

  public function verifyUser(User $user): User
  {
    $user->forceFill([
      'email_verified_at' => now(),
      'activation' => 1,
      'activation_date' => now()
    ])->save();

    return $user;
  }

  public function assignRole(User $user, string|int|array|Role|Collection $roles)
  {
    return $user->assignRole($roles);
  }

  public function rolesSync(User $user, array $roles)
  {
    return $user->syncRoles($roles);

  }
  public function permissionsSync(User $user, array $permissions)
  {
    return $user->syncPermissions($permissions);
  }

  public function detachRoles(User $user)
  {
    return $user->roles()->detach();
  }
  public function detachPermissions(User $user)
  {
    return $user->permissions()->detach();
  }

  public function syncDepartments(User $user, array $data)
  {
    return $user->departments()->sync($data);
  }
  public function deleteAdmin(User $admin)
  {
    return DB::transaction(function () use ($admin) {
      $this->detachPermissions($admin);
      $this->detachRoles($admin);
      return $this->delete($admin);
    });
  }


}