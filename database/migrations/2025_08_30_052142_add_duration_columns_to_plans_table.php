<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'plans',
            function (Blueprint $table) {
                $table->integer('duration')->nullable();
                $table->string('duration_unit')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'plans',
            function (Blueprint $table) {
                $table->dropColumn('duration');
                $table->dropColumn('duration_unit');
            }
        );
    }
};
