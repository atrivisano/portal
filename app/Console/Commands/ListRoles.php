<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ListRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:list {--with-permissions : Display permissions for each role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available roles and their associated permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->info('No roles found in the system.');

            return 0;
        }

        $this->info('Available roles:');

        if ($this->option('with-permissions')) {
            // Display roles with their permissions
            $rolesWithPermissions = $roles->map(function ($role) {
                $permissions = $role->permissions->pluck('name')->join(', ');

                return [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'permissions' => $permissions ?: 'None',
                    'users_count' => $role->users->count()
                ];
            });

            $this->table(
                ['ID', 'Name', 'Permissions', 'Users Count'],
                $rolesWithPermissions
            );
        } else {
            // Display just the roles
            $this->table(
                ['ID', 'Name', 'Users Count'],
                $roles->map(fn($role) => [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'users_count' => $role->users->count()
                ])
            );
        }

        // Show total count
        $this->info("Total roles: {$roles->count()}");

        // Provide usage hint for the assign role command
        $this->comment('To assign a role to a user, use:');
        $this->comment('  php artisan user:assign-role {user} {role}');

        return 0;
    }
}
