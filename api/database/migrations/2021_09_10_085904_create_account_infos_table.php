<?php

use App\Models\Client\ClientAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ClientAccount::class)->constrained()->cascadeOnDelete();


            $table->year('year');
            $table->string('quarter');

            $table->double('outstanding_fcy', 30, 10)->nullable();
            $table->double('outstanding_lcy', 30, 10)->nullable();
            $table->double('accrued_interest_lcy', 30, 10)->nullable();
            $table->double('suspended_lcy', 30, 10)->nullable();
            $table->double('interest_received_in_advance_lcy', 30, 10)->nullable();
            $table->date('st_date')->nullable();
            $table->date('mat_date')->nullable();
            $table->date('sp_date')->nullable();
            $table->double('past_due_days')->nullable();
            $table->double('number_of_reschedule')->nullable();
            $table->double('cm_guarantee', 30, 10)->nullable();
            $table->double('estimated_value_of_stock_collateral', 30, 10)->nullable();
            $table->double('pv_securities_guarantees', 30, 10)->nullable();
            $table->double('mortgages', 30, 10)->nullable();
            $table->double('estimated_value_of_real_estate_collateral', 30, 10)->nullable();
            $table->double('80_per_estimated_value_of_real_estate_collateral', 30, 10)->nullable();
            $table->double('pv_re_guarantees', 30, 10)->nullable();
            $table->double('interest_rate', 30, 10)->nullable();
            $table->string('pay_method')->nullable();
            $table->double('number_of_installments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_infos');
    }
}
