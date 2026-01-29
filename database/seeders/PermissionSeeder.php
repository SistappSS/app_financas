<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([]);

        $userRole = Role::firstOrCreate(['name' => 'additional_user']);
        $userRole->syncPermissions([]);
    }
}
