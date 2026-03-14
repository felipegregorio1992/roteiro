<?php

namespace App\Policies;

use App\Models\Scene;
use App\Models\User;

class ScenePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Scene $scene): bool
    {
        $project = $scene->project;
        if (! $project) {
            return false;
        }

        return $user->can('view', $project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Scene $scene): bool
    {
        $project = $scene->project;
        if (! $project) {
            return false;
        }

        return $user->can('update', $project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Scene $scene): bool
    {
        $project = $scene->project;
        if (! $project) {
            return false;
        }

        return $user->can('update', $project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Scene $scene): bool
    {
        return $this->update($user, $scene);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Scene $scene): bool
    {
        return $this->update($user, $scene);
    }
}
