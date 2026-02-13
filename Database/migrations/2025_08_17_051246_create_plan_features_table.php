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
            'plan_features',
            function (Blueprint $table) {
                $table->id();
                $table->uuid('plan_id')->index();
                $table->string('name', 50)->index();
                $table->string('value', 150)->nullable();
                $table->foreign('plan_id')
                    ->references('id')
                    ->on('plans')
                    ->onDelete('cascade');
                $table->datetimes();

                $table->unique(['plan_id', 'name']);
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
        Schema::dropIfExists('plan_features');
    }
};
