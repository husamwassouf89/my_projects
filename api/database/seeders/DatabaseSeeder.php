<?php

namespace Database\Seeders;

use App\Models\Client\ClassType;
use App\Models\Client\Currency;
use App\Models\Client\Type;
use App\Models\Permission\Permission;
use App\Models\Permission\PermissionRole;
use App\Models\Permission\Role;
use App\Models\User;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // User Permissions
        Permission::create(['name' => 'Add User',]);
        Permission::create(['name' => 'Edit User']);
        Permission::create(['name' => 'Delete User']);
        Permission::create(['name' => 'View all Users']);


        // Role Permissions
        Permission::create(['name' => 'Add Role']);
        Permission::create(['name' => 'Edit Role']);
        Permission::create(['name' => 'Show Role']);
        Permission::create(['name' => 'Delete Role']);
        Permission::create(['name' => 'View all Roles']);


        // Client Permissions
        Permission::create(['name' => 'Import Clients']);
        Permission::create(['name' => 'View all Clients']);
        Permission::create(['name' => 'View Client']);


        // this can be done as separate statements
        $role = Role::create(['name' => 'Data Entry']);
        Permission::all()->each(function ($permission) use ($role) {
            $role->permissions()->attach($permission->id);
        });

        User::create([
                         'name'     => 'Super Admin',
                         'email'    => 'super-admin@ifrs.com',
                         'password' => '123456',
                         'role_id'  => '1',
                     ]);

        $classType = ClassType::create(['name' => 'Corporate']);
        for ($i = 1; $i <= 10; $i++) {
            $classType->grades()->create(['serial_no' => $i - 1, 'name' => $i]);
        }
        $classType = ClassType::create(['name' => 'Middle']);
        for ($i = 1; $i <= 10; $i++) {
            $classType->grades()->create(['serial_no' => $i - 1, 'name' => $i]);
        }
        $classType = ClassType::create(['name' => 'SME`s']);
        for ($i = 0; $i < 10; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $i]);
        }
        $classType = ClassType::create(['name' => 'Retail']);
        for ($i = 1; $i <= 3; $i++) {
            $classType->grades()->create(['serial_no' => $i - 1, 'name' => $i]);
        }
        $list = ['AAA', 'AA', 'A', 'BBB', 'BB', 'B', 'CCC/C', 'Default'];

        $classType = ClassType::create(['name' => 'Internal_FI']);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        $classType = ClassType::create(['name' => 'External_FI']);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        $classType = ClassType::create(['name' => 'Sovereign']);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        Type::create(['name' => 'Loans']);
        Type::create(['name' => 'ADAs']);
        Type::create(['name' => 'DB']);
        Type::create(['name' => 'OD']);

        Currency::create(['code' => '001', 'name' => 'IQD']);
        Currency::create(['code' => '002', 'name' => 'USD']);

    }
}
