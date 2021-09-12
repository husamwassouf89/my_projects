<?php

use App\Models\Staging\Stage;
use App\Models\Staging\StagingOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagingOptionResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staging_option_results', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StagingOption::class)->constrained()->cascadeOnDelete();
            $table->float('range_start')->nullable();
            $table->float('range_end')->nullable();
            $table->foreignIdFor(Stage::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staging_option_results');
    }
}
