<?php

namespace Database\Seeders;

use App\Models\Client\ClassType;
use App\Models\Client\ClientType;
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

        ClassType::create(['name' => 'Corporate']);
        ClassType::create(['name' => 'Middle']);
        ClassType::create(['name' => 'Retail']);
        ClassType::create(['name' => 'SME\'s']);


        Type::create(['name' => 'Loans']);
        Type::create(['name' => 'ADAs']);
        Type::create(['name' => 'DB']);
        Type::create(['name' => 'OD']);

        Currency::create(['code'=>'001','name' => 'IQD']);
        Currency::create(['code'=>'002','name' => 'USD']);





    }
}
