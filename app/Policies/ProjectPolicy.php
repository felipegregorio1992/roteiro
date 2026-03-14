<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project)
    {
        if ($user->id === $project->user_id) {
            return true;
        }

        return $project->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, Project $project)
    {
        if ($user->id === $project->user_id) {
            return true;
        }

        return $project->members()
            ->whereKey($user->id)
            ->wherePivot('role', 'editor')
            ->exists();
    }

    public function delete(User $user, Project $project)
    {
        return $user->id === $project->user_id;
    }
}
