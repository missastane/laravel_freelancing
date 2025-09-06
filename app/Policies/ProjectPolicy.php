<?php

namespace App\Policies;

use App\Models\Market\Project;
use App\Models\User\User;

class ProjectPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

     /**
     * Determine whether the user can toggle the model full time status.
     */
    public function toggleFullTime(User $user)
    {
        return $user->activeSubscription();
    }

    public function update(User $user,Project $project)
    {
        \Log::info($project);
        return $project->status == 1;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->employer->id == $user->id || $user->hasRole('admin');
    }


}
