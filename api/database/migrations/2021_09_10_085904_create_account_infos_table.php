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

            $table->double('outstanding_fcy')->nullable();
            $table->double('outstanding_lcy')->nullable();
            $table->double('accrued_interest_lcy')->nullable();
            $table->double('suspended_lcy')->nullable();
            $table->double('interest_received_in_advance_lcy')->nullable();
            $table->date('st_date')->nullable();
            $table->date('mat_date')->nullable();
            $table->date('sp_date')->nullable();
            $table->double('past_due_days')->nullable();
            $table->double('number_of_reschedule')->nullable();
            $table->double('cm_guarantee')->nullable();
            $table->double('estimated_value_of_stock_collateral')->nullable();
            $table->double('pv_securities_guarantees')->nullable();
            $table->double('mortgages')->nullable();
            $table->double('estimated_value_of_real_estate_collateral')->nullable();
            $table->double('80_per_estimated_value_of_real_estate_collateral')->nullable();
            $table->double('pv_re_guarantees')->nullable();
            $table->double('interest_rate',20,20)->nullable();
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
