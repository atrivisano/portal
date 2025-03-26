<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log user creation
        User::created(function ($user) {
            ActivityLog::log(
                'created',
                'user',
                $user->id,
                $user->name,
                ['email' => $user->email],
                'User account created'
            );
        });

        // Log user updates
        User::updated(function ($user) {
            $dirtyFields = array_keys($user->getDirty());
            if (count($dirtyFields) > 0) {
                ActivityLog::log(
                    'updated',
                    'user',
                    $user->id,
                    $user->name,
                    ['updated_fields' => $dirtyFields],
                    'User account updated'
                );
            }
        });

        // Log role creation
        Role::created(function ($role) {
            ActivityLog::log(
                'created',
                'role',
                $role->id,
                $role->name,
                null,
                'Role created'
            );
        });

        // Log role updates
        Role::updated(function ($role) {
            $dirtyFields = array_keys($role->getDirty());
            if (count($dirtyFields) > 0) {
                ActivityLog::log(
                    'updated',
                    'role',
                    $role->id,
                    $role->name,
                    ['updated_fields' => $dirtyFields],
                    'Role updated'
                );
            }
        });
    }
}
