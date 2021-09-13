<?php

namespace Database\Seeders;

use App\Models\Staging\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Stage::create(['serial_no' => 1, 'name' => 'مرحلة 1']);
        Stage::create(['serial_no' => 2, 'name' => 'مرحلة 2']);
        Stage::create(['serial_no' => 3, 'name' => 'مرحلة 3']);
    }
}
