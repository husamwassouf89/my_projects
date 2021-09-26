<?php

use App\Models\Client\ClassType;
use App\Models\Client\Guarantee;
use App\Models\Staging\Stage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuaranteeLGDSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantee_l_g_d_s', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Guarantee::class);
            $table->foreignIdFor(Stage::class);
            $table->foreignIdFor(ClassType::class);
            $table->float('ratio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guarantee_l_g_d_s');
    }
}
