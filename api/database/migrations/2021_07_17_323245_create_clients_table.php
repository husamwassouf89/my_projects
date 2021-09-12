<?php

use App\Models\Client\Branch;
use App\Models\Client\ClassType;
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
            $table->foreignIdFor(Branch::class);
            $table->foreignIdFor(ClassType::class);
            $table->string('name');
            $table->enum('financial_data', ['Yes', 'No'])->default('No');

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
