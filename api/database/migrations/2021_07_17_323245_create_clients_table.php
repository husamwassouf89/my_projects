<?php

use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Currency;
use App\Models\Client\Type;
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
            $table->string('loan_key');
            $table->string('cif');
            $table->foreignIdFor(Branch::class);
            $table->foreignIdFor(ClassType::class);
            $table->foreignIdFor(Type::class);
            $table->string('name');
            $table->foreignIdFor(Currency::class, 'main_currency_id');
            $table->double('outstanding_fcy')->nullable();
            $table->double('outstanding_lcy')->nullable();
            $table->double('accrued_interest_lcy')->nullable();
            $table->double('suspended_lcy')->nullable();
            $table->double('interest_received_in_advance_lcy')->nullable();
            $table->date('st_date')->nullable();
            $table->date('mat_date')->nullable();
            $table->date('sp_date')->nullable();
            $table->date('past_due_days')->nullable();
            $table->date('number_of_reschedule')->nullable();
            $table->date('guarantee_ccy')->nullable();
            $table->date('cm_guarantee')->nullable();
            $table->date('estimated_value_of_stock_collateral')->nullable();
            $table->date('pv_securities_guarantees')->nullable();
            $table->date('mortgages')->nullable();
            $table->date('estimated_value_of_real_estate_collateral')->nullable();
            $table->date('80_per_estimated_value_of_real_estate_collateral')->nullable();
            $table->date('pv_re_guarantees')->nullable();
            $table->date('interest_rate')->nullable();
            $table->date('pay_method')->nullable();
            $table->date('number_of_installments')->nullable();
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
