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
//            $table->string('loan_key');
            $table->string('cid');
//            $table->foreignIdFor(Branch::class);
//            $table->foreignIdFor(ClassType::class);
//            $table->foreignIdFor(Type::class);
            $table->string('name');

//            $table->foreignIdFor(Currency::class, 'main_currency_id');
//            $table->double('outstanding_fcy')->nullable();
//            $table->double('outstanding_lcy')->nullable();
//            $table->double('accrued_interest_lcy')->nullable();
//            $table->double('suspended_lcy')->nullable();
//            $table->double('interest_received_in_advance_lcy')->nullable();
//            $table->date('st_date');
//            $table->date('mat_date');
//            $table->date('sp_date');
//            $table->date('past_due_days');
//            $table->date('number_of_reschedule');
//            $table->date('past_due_days');
//            $table->date('guarantee_ccy');
//            $table->date('cm_guarantee');
//            $table->date('');
//            $table->date('');
//            $table->date('');
//            $table->date('');
//            $table->date('');
//            $table->date('');
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
