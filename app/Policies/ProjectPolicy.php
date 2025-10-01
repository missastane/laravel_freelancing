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

    public function getProjectProposals(User $user, Project $project)
    {
        return ($user->id == $project->user_id) || ($user->active_role === 'admin' && $user->user_type == 2);
    }
    /**
     * Determine whether the user can toggle the model full time status.
     */
    public function toggleFullTime(User $user)
    {
        return $user->activeSubscription();
    }

    public function update(User $user, Project $project)
    {
        return $project->status == 1 && $user->id == $project->user_id;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($project->status != 1) {
            return false;
        }
        return ($project->employer->id == $user->id) || ($user->user_type == 2 && $user->active_role === 'admin');
    }

    public function storeProposal(User $user, Project $project)
    {
        return $project->status == 1;
    }

}
