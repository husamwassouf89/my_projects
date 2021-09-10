<?php

use App\Models\Staging\StagingOption;
use App\Models\Staging\StagingQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagingOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staging_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StagingQuestion::class)->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->enum('type', StagingOption::$TYPES);
            $table->enum('with_value', ['Yes','No'])->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staging_options');
    }
}
