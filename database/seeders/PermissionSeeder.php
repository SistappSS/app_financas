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

        Role::firstOrCreate(['name' => 'trials'])->syncPermissions([]);
        Role::firstOrCreate(['name' => 'subscript'])->syncPermissions([]);
        Role::firstOrCreate(['name' => 'user'])->syncPermissions([]);
        Role::firstOrCreate(['name' => 'additional_user'])->syncPermissions([]);
    }
}
