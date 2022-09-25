<?php

use App\Models\Client\Client;
use App\Models\Client\Currency;
use App\Models\Client\DocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('limits', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class);
            $table->foreignIdFor(Currency::class, 'direct_limit_currency_id')->nullable();
            $table->foreignIdFor(Currency::class, 'un_direct_limit_currency_id')->nullable();
            $table->double('general_limit_lcy')->default(0);
            $table->double('direct_limit_lcy')->default(0);
            $table->double('un_direct_limit_lcy')->default(0);
            $table->enum('cancellable', ['yes', 'no']);
            $table->year('year');
            $table->string('quarter');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('limits');
    }
}
