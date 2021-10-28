<?php

use App\Models\IRS\IRS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(IRS::class, 'irs_id');
            $table->enum('financial_status', \App\Models\Client\Client::$FINANCIAL_STATUS)->default(\App\Models\Client\Client::$FINANCIAL_STATUS[0]);
            $table->string('text');
            $table->float('max_options_value')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
