<?php

use App\Models\Client\Client;
use App\Models\Client\Currency;
use App\Models\Client\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnDelete();
            $table->string('loan_key');
            $table->foreignIdFor(Type::class);
            $table->foreignIdFor(Currency::class, 'main_currency_id');
            $table->double('outstanding_fcy')->nullable();
            $table->double('outstanding_lcy')->nullable();
            $table->double('accrued_interest_lcy')->nullable();
            $table->double('suspended_lcy')->nullable();
            $table->double('interest_received_in_advance_lcy')->nullable();
            $table->date('st_date')->nullable();
            $table->date('mat_date')->nullable();
            $table->date('sp_date')->nullable();
            $table->string('past_due_days')->nullable();
            $table->string('number_of_reschedule')->nullable();
            $table->string('guarantee_ccy')->nullable();
            $table->string('cm_guarantee')->nullable();
            $table->string('estimated_value_of_stock_collateral')->nullable();
            $table->string('pv_securities_guarantees')->nullable();
            $table->string('mortgages')->nullable();
            $table->string('estimated_value_of_real_estate_collateral')->nullable();
            $table->string('80_per_estimated_value_of_real_estate_collateral')->nullable();
            $table->string('pv_re_guarantees')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('pay_method')->nullable();
            $table->string('number_of_installments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_accounts');
    }
}
