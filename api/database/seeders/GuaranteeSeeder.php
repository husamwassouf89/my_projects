<?php

namespace Database\Seeders;

use App\Models\Client\Guarantee;
use App\Models\Client\GuaranteeLGD;
use Illuminate\Database\Seeder;

class GuaranteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $guarantee = Guarantee::create(['code' => 'cm', 'name' => 'نقدي', 'order' => '1',]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 1, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 2, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 3, 'ratio' => 0]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 1, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 2, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 3, 'ratio' => 0]);


        $guarantee = Guarantee::create(['code' => 'sm', 'name' => 'امان', 'order' => '2',]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 1, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 2, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 3, 'ratio' => 0]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 1, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 2, 'ratio' => 0]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 3, 'ratio' => 0]);


        $guarantee = Guarantee::create(['code'  => 're', 'name'  => 'عقار', 'order' => '3']);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 1, 'ratio' => 0.05]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 2, 'ratio' => 0.1]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 3, 'ratio' => 0.1]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 1, 'ratio' => 0.05]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 2, 'ratio' => 0.1]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 3, 'ratio' => 0.1]);


        $guarantee = Guarantee::create(['code' => 'un', 'name' => 'غير مغطى', 'order' => '4',]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 1, 'ratio' => 0.2]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 2, 'ratio' => 0.4]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 3, 'ratio' => 1]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 1, 'ratio' => 0.25]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 2, 'ratio' => 0.50]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 4, 'stage_id' => 3, 'ratio' => 1]);

        $guarantee = Guarantee::create(['code' => 'Subordinated', 'name' => 'المُساند', 'order' => '5',]);

        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 1, 'ratio' => 0.75]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 2, 'ratio' => 0.75]);
        GuaranteeLGD::create(['guarantee_id' => $guarantee->id, 'class_type_id' => 1, 'stage_id' => 3, 'ratio' => 0.75]);
    }
}
