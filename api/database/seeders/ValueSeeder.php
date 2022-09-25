<?php

namespace Database\Seeders;

use App\Models\Client\Predefined;
use App\Models\Value;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Seeder;

class ValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Value::create(['value' => 1,'description' => 'Activate min value as Due Days for LGD']);

        Value::create(['value' => 0.2,'description' => 'More than 90 days']);
        Value::create(['value' => 0.5,'description' => 'More than 180 days']);
        Value::create(['value' => 1,'description' => 'More than 360 days']);
    }
}
