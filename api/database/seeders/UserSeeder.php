<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Data Entry']);

        Permission::create(['name' => 'Settings']);

        // this can be done as separate statements
        Permission::all()->each(function ($permission) use ($role) {
            $role->permissions()->attach($permission->id);
        });

        User::create([
                         'name'     => 'Master Admin',
                         'email'    => 'setting-admin@ifrs.com',
                         'password' => '123456',
                         'role_id'  => $role->id,
                     ]);

    }
}
