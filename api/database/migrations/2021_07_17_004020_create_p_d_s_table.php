<?php

use App\Models\Client\ClassType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePDSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_d_s', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ClassType::class)->constrained();
            $table->year('year');
            $table->string('quarter');
            $table->string('path');

            $table->float('eco_parameter_base_value');
            $table->float('eco_parameter_mild_value');
            $table->float('eco_parameter_heavy_value');

            $table->float('eco_parameter_base_weight');
            $table->float('eco_parameter_mild_weight');
            $table->float('eco_parameter_heavy_weight');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('p_d_s');
    }
}
