<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;

class CharacterPolicy
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
    public function view(User $user, Character $character): bool
    {
        $project = $character->project;
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
    public function update(User $user, Character $character): bool
    {
        $project = $character->project;
        if (! $project) {
            return false;
        }

        return $user->can('update', $project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Character $character): bool
    {
        return $this->update($user, $character);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Character $character): bool
    {
        return $this->update($user, $character);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Character $character): bool
    {
        return $this->update($user, $character);
    }
}
