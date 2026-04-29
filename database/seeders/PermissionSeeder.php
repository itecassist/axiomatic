<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'view commission notes']);
        Permission::firstOrCreate(['name' => 'manage commission notes']);
        Permission::firstOrCreate(['name' => 'manage companies']);
        Permission::firstOrCreate(['name' => 'manage branches']);
        Permission::firstOrCreate(['name' => 'manage employees']);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions(['view commission notes']);

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions(['view commission notes', 'manage commission notes']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}
