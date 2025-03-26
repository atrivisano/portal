<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class UserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:roles {user? : The ID or email of the user} {--all : List all users with their roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List roles assigned to a user or all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if the --all flag is set
        if ($this->option('all')) {
            return $this->listAllUsersWithRoles();
        }

        // Get user by ID or email
        $userIdentifier = $this->argument('user');

        if (!$userIdentifier) {
            $userIdentifier = $this->ask('Enter user ID or email');
        }

        // Determine if the user identifier is an email or an ID
        $validator = Validator::make(['user' => $userIdentifier], [
            'user' => 'email',
        ]);

        // Find the user
        $user = $validator->passes()
            ? User::where('email', $userIdentifier)->first()
            : User::find($userIdentifier);

        if (!$user) {
            $this->error("User not found with the provided identifier: {$userIdentifier}");

            return 1;
        }

        $this->info("Roles assigned to {$user->name} ({$user->email}):");

        $roles = $user->roles;

        if ($roles->isEmpty()) {
            $this->warn("This user has no roles assigned.");

            return 0;
        }

        $this->table(
            ['ID', 'Role', 'Permissions'],
            $roles->map(function ($role) {
                $permissions = $role->permissions->pluck('name')->join(', ');

                return [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'permissions' => $permissions ?: 'None'
                ];
            })
        );

        return 0;
    }

    /**
     * List all users with their roles.
     */
    private function listAllUsersWithRoles()
    {
        $users = User::with('roles')->get();

        if ($users->isEmpty()) {
            $this->info('No users found in the system.');

            return 0;
        }

        $usersData = $users->map(function ($user) {
            $roles = $user->roles->pluck('name')->join(', ');

            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $roles ?: 'None',
                'is_approved' => $user->is_approved ? 'Yes' : 'No'
            ];
        });

        $this->info('Users and their roles:');
        $this->table(
            ['ID', 'Name', 'Email', 'Roles', 'Approved'],
            $usersData
        );

        $this->info("Total users: {$users->count()}");

        return 0;
    }
}
