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
        Schema::create(
            'plans',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->decimal('price', 15, 2)->index()->default(0);
                $table->integer('duration')->nullable();
                $table->string('duration_unit')->nullable();
                $table->boolean('is_free')->default(false)->index();
                $table->boolean('active')->index()->default(true);
                $table->string('module', 50)->index();
                $table->datetimes();
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
        Schema::dropIfExists('plan_translations');
        Schema::dropIfExists('plans');
    }
};
