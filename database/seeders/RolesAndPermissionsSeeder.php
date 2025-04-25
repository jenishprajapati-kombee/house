<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Permission Names (using Filament/standard conventions)
        $permissions = [
            'viewAny house',
            'view house',
            'create house',
            'update house',
            'delete house',
            // Add other permissions if needed, e.g., 'manage users'
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web'); // Use 'web' guard
        }

        // Create Roles
        $adminRole = Role::findOrCreate('Admin', 'web');
        $userRole = Role::findOrCreate('User', 'web');

        // Assign Permissions to Roles
        // Admin gets all house permissions
        $adminRole->givePermissionTo([
            'viewAny house',
            'view house',
            'create house',
            'update house',
            'delete house',
        ]);

        // User gets only view permissions
        $userRole->givePermissionTo([
            'viewAny house',
            'view house',
        ]);

        // --- Create Fixed Admin User ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123456'),
                'mobile_number' => '0000000000', // Add a placeholder or make nullable
                'nationality' => 'N/A'         // Add a placeholder or make nullable
            ]
        );
        $adminUser->assignRole($adminRole); // Assign Admin role

         // Optional: Create a default user for testing
        //  $testUser = User::firstOrCreate(
        //      ['email' => 'user@gmail.com'],
        //      [
        //          'name' => 'Test User',
        //          'password' => Hash::make('password'),
        //          'mobile_number' => '1111111111',
        //          'nationality' => 'Testland'
        //      ]
        //  );
        //  $testUser->assignRole($userRole);
    }
}