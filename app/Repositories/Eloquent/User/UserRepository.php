<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\ResourceCollections\UserResourceCollection;
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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
      ->orderBy('last_name');
  }


  public function getUsers(int $type, $message)
  {
    $users = $this->getUsersQuery($type)->paginate(15);
    return new BaseCollection($users, UserResource::class, $message);

  }

  public function searchUsers(int $type, string $search, $message)
  {
    $users = $this->getUsersQuery($type)
      ->where(function ($query) use ($search) {
        $query->where('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%");
      })
      ->paginate(15);
    return new BaseCollection($users, UserResource::class, $message);
  }
  public function showUser(User $user)
  {
    $user = $this->showWithRelations($user, ['roles:id,name', 'permissions:id,name']);

    return new UserResource($user);
  }

  public function findByEmail(string $email): User
  {
    return $this->model->where('email', $email)->whereNotNull('email_verified_at')->first();
  }

  public function toggleActivation(User $user): string|null
  {
    $user->activation = $user->activation == 1 ? 2 : 1;
    if ($user->save()) {
      $message = $user->activation == 1
        ? 'کاربر با موفقیت فعال شد'
        : 'کاربر با موفقیت غیرفعال شد';
      return $message;
    }
    return null;
  }

  public function findByMobile(string $mobile): User
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
     $user->roles()->sync($roles);
     
  }
  public function permissionsSync(User $user, array $permissions)
  {
    return $user->permissions()->sync($permissions);
  }

  public function detachRoles(User $user)
  {
    return $user->roles()->detach();
  }
  public function detachPermissions(User $user)
  {
    return $user->permissions()->detach();
  }

  public function deleteAdmin(User $admin)
  {
    return DB::transaction(function()use($admin){
      $this->detachPermissions($admin);
      $this->detachRoles($admin);
      return $this->delete($admin);
    });
  }


}