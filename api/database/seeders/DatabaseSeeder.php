<?php

namespace Database\Seeders;

use App\Models\Client\ClassType;
use App\Models\Client\Currency;
use App\Models\Client\DocumentType;
use App\Models\Client\Predefined;
use App\Models\Client\Type;
use App\Models\IRS\Category;
use App\Models\Permission\Permission;
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

        $classType = ClassType::create(['name' => 'Internal_FI', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        $classType = ClassType::create(['name' => 'Local Bank', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            if ($i == 7) {
                $lgd = 1;
            } else {
                $lgd = 0.45;
            }
            $grade = $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
            Predefined::create(['stage_id' => 1, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);
            Predefined::create(['stage_id' => 2, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);
            Predefined::create(['stage_id' => 3, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);

        }

        $classType = ClassType::create(['name' => 'Abroad Bank', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            if ($i == 7) {
                $lgd = 1;
            } else {
                $lgd = 0.45;
            }
            $grade = $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
            Predefined::create(['stage_id' => 1, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);
            Predefined::create(['stage_id' => 2, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);
            Predefined::create(['stage_id' => 3, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd]);

        }

        $classType = ClassType::create(['name' => 'Investments', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        $classType = ClassType::create(['name' => 'Central Bank', 'fixed' => 1]);
        for ($i = 0; $i < 1; $i++) {
            $grade = $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
            Predefined::create(['stage_id' => 1, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd, 'pd' => '0']);
            Predefined::create(['stage_id' => 1, 'grade_id' => $grade->id, 'class_type_id' => $classType->id, 'lgd' => $lgd, 'pd' => '0.045', 'currency_type' => 'foreign']);
        }
        $classType = ClassType::create(['name' => 'External_FI', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        $classType = ClassType::create(['name' => 'Sovereign', 'fixed' => 1]);
        for ($i = 0; $i < 8; $i++) {
            $classType->grades()->create(['serial_no' => $i, 'name' => $list[$i]]);
        }

        Type::create(['name' => 'Loans']);
        Type::create(['name' => 'ADAs']);
        Type::create(['name' => 'DB']);
        Type::create(['name' => 'OD']);

        Currency::create(['code' => '001', 'name' => 'IQD', 'type' => Currency::$TYPES[1]]);
        Currency::create(['code' => '002', 'name' => 'USD']);


        Category::create(['name' => 'Quantity']);
        Category::create(['name' => 'Quality']);
        Category::create(['name' => 'Facilitation']);


        DocumentType::create(['name' => 'اعتماد مستندي-مؤجل الدفع يتجاوز آجله 180يوم', 'ccf' => 1]);
        DocumentType::create(['name' => 'اعتماد مستندي-غب الاطلاع لا يتجاوز آجله 180يوم', 'ccf' => 0.2]);
        DocumentType::create(['name' => 'كفالة دفع (LG - Payment Guarantee)', 'ccf' => 1]);
        DocumentType::create(['name' => 'كفالة حسن تنفيد (LG - Performance Bond)', 'ccf' => 0.5]);
        DocumentType::create(['name' => 'كفالة دخول مناقصة (LG - Bid Bond)', 'ccf' => 0.5]);
        DocumentType::create(['name' => 'تمويل شراء الجزء غير المكتتب به من إصدارات الأوراق المالية', 'ccf' => 0.5]);
        DocumentType::create(['name' => 'مستندات برسم التحصيل', 'ccf' => 0]);
    }
}
