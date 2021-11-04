<?php

use App\Models\Client\ClassType;
use App\Models\IRS\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIRSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_r_s', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('IRS for certain class type');
            $table->enum('financial_status', \App\Models\Client\Client::$FINANCIAL_STATUS)->default(\App\Models\Client\Client::$FINANCIAL_STATUS[0]);
            $table->float('percentage')->default(0);
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(ClassType::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_r_s');
    }
}
