<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubCategoryToClassTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_types', function (Blueprint $table) {
            $table->enum('sub_category', ['facility','retail','local bank','investments','abroad bank','central bank']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_types', function (Blueprint $table) {
            $table->dropColumn('sub_category');
        });
    }
}
