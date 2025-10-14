<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can edit the model.
     */
    public function edit(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }
    /**
     * Determine whether the user can impersonate the model.
     */
    public function impersonate(User $user)
    {
        return $user->level === User::LEVEL_SUPER_ADMIN;
    }
}
