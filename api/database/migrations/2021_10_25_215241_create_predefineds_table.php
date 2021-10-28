<?php

use App\Models\Client\ClassType;
use App\Models\Client\Grade;
use App\Models\Staging\Stage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePredefinedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('predefineds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Grade::class);
            $table->foreignIdFor(ClassType::class);
            $table->foreignIdFor(Stage::class);
            $table->double('pd', 20, 20)->nullable();
            $table->float('lgd', 20, 20)->nullable();
            $table->enum('currency_type', ['local', 'foreign'])->default('local');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('predefineds');
    }
}
