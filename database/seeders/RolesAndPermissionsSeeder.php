<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions grouped by module
        $permissionGroups = [
            'users' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
            ],
            'roles' => [
                'view roles',
                'create roles',
                'edit roles',
                'delete roles',
            ],
            'permissions' => [
                'view permissions',
                'assign permissions',
            ],
            'profile' => [
                'view profile',
                'edit profile',
            ],
            'admin' => [
                'access admin dashboard',
                'view system logs',
                'view system settings',
                'edit system settings',
            ],
        ];

        // Flatten permissions for creation
        $permissions = collect($permissionGroups)->flatten()->toArray();

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create super-admin role and assign no permissions (will use Gate::before)
        $superAdminRole = Role::create(['name' => 'super-admin']);

        // Create admin role with specific permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'view permissions',
            'view profile',
            'edit profile',
            'access admin dashboard',
            'view system logs',
            'view system settings',
        ]);

        // Create volunteer role with limited permissions
        $volunteerRole = Role::create(['name' => 'volunteer']);
        $volunteerRole->givePermissionTo([
            'view profile',
            'edit profile',
        ]);

        // Create super admin user
        $superAdmin = User::factory()->superAdmin()->create();
        $superAdmin->assignRole('super-admin');

        // Create admin user
        $admin = User::factory()->admin()->create();
        $admin->assignRole('admin');

        // Create volunteer user
        $volunteer = User::factory()->volunteer()->create();
        $volunteer->assignRole('volunteer');

        // Create some random users with roles
        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('volunteer');
        });

        // Create some pending approval users
        User::factory()
            ->count(3)
            ->pendingApproval()
            ->create()
            ->each(function ($user) {
                $user->assignRole('volunteer');
            });
    }
}
