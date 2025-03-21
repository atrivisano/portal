<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user, Role $role): bool
    {
        // Super-admin role can only be edited by super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('edit roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user, Role $role): bool
    {
        // Super-admin role cannot be deleted
        if ($role->name === 'super-admin') {
            return false;
        }

        return $user->hasPermissionTo('delete roles');
    }
}
