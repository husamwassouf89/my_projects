<?php

use App\Models\Staging\ClientStagingProfile;
use App\Models\Staging\StagingOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagingAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staging_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StagingOption::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ClientStagingProfile::class)->constrained()->cascadeOnDelete();
            $table->float('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staging_answers');
    }
}
