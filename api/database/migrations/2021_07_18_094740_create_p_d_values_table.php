<?php

use App\Models\Client\Grade;
use App\Models\PD\PD;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePDValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_d_values', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PD::class);
            $table->foreignIdFor(Grade::class, 'row_id');
            $table->foreignIdFor(Grade::class, 'column_id')->nullable();
            $table->enum('column_type', ['grade', 'total'])->default('grade');
            $table->double('float');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('p_d_values');
    }
}
