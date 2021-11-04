<?php

use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Grade;
use App\Models\Staging\Stage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('cif');
            $table->foreignIdFor(Branch::class)->nullable();
            $table->foreignIdFor(ClassType::class);
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->foreignIdFor(Grade::class)->nullable();
            $table->foreignIdFor(Stage::class)->nullable();
            $table->enum('financial_status', \App\Models\Client\Client::$FINANCIAL_STATUS)->default(\App\Models\Client\Client::$FINANCIAL_STATUS[0]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
