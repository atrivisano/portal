<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super-admin can only be edited by another super-admin
        if ($model->hasRole('super-admin') && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('edit users');
    }

    /**
     * Determine whether the user can update the roles of a model.
     */
    public function updateRoles(User $user, User $model): bool
    {
        // Super-admin roles can only be edited by another super-admin
        if ($model->hasRole('super-admin') && !$user->hasRole('super-admin')) {
            return false;
        }

        // Prevent users from updating their own roles
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('assign permissions');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Super-admin can only be deleted by another super-admin
        if ($model->hasRole('super-admin') && !$user->hasRole('super-admin')) {
            return false;
        }

        // Prevent users from deleting themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('delete users');
    }
}
