<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AssignRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role
                            {user : The ID or email of the user}
                            {role : The name of the role to assign}
                            {--remove : Remove the role from the user instead of assigning it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user or remove a role from a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the user identifier and role from the command arguments
        $userIdentifier = $this->argument('user');
        $roleName = $this->argument('role');
        $isRemove = $this->option('remove');

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

        // Find the role
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("Role not found: {$roleName}");
            $this->info("Available roles:");

            $roles = Role::all();
            $this->table(
                ['ID', 'Name'],
                $roles->map(fn($role) => [$role->id, $role->name])
            );

            return 1;
        }

        // Assign or remove the role
        if ($isRemove) {
            if (!$user->hasRole($role)) {
                $this->warn("User {$user->name} ({$user->email}) does not have the role: {$role->name}");

                return 0;
            }

            $user->removeRole($role);
            $this->info("Successfully removed role {$role->name} from user {$user->name} ({$user->email})");
        } else {
            if ($user->hasRole($role)) {
                $this->warn("User {$user->name} ({$user->email}) already has the role: {$role->name}");

                return 0;
            }

            $user->assignRole($role);
            $this->info("Successfully assigned role {$role->name} to user {$user->name} ({$user->email})");
        }

        // Show the user's current roles
        $this->info("Current roles for user {$user->name}:");
        $this->table(
            ['Role'],
            $user->roles->map(fn($role) => [$role->name])
        );

        return 0;
    }
}
