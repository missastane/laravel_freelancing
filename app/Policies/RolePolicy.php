<?php

namespace App\Policies;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
   

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $role->name !== 'admin' && $role->name !== 'superadmin';
    }

}
