<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Profile management
            'view profile',
            'edit profile',

            // Permission management
            'view permissions',
            'assign permissions',

            // Admin dashboard
            'access admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        // Super admin gets all permissions automatically via Gate::before rule

        // Admin role
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
        ]);

        // Volunteer role
        $volunteerRole = Role::create(['name' => 'volunteer']);
        $volunteerRole->givePermissionTo([
            'view profile',
            'edit profile',
        ]);

        // Create a super-admin user if it doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->assignRole('super-admin');

        // Create an admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        // Create a volunteer user if it doesn't exist
        $volunteer = User::firstOrCreate(
            ['email' => 'volunteer@example.com'],
            [
                'name' => 'Volunteer User',
                'email' => 'volunteer@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $volunteer->assignRole('volunteer');
    }
}
