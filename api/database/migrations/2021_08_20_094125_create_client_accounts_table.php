<?php

use App\Models\Client\Client;
use App\Models\Client\Currency;
use App\Models\Client\DocumentType;
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
            $table->foreignIdFor(DocumentType::class)->nullable();
            $table->foreignIdFor(Currency::class, 'main_currency_id');
            $table->foreignIdFor(Currency::class, 'guarantee_currency_id')->nullable();

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
