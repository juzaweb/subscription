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
                $table->decimal('price', 15, 2)->index()->default(0);
                $table->boolean('is_free')->default(false)->index();
                $table->string('status', 50)->index()->default('draft');
                $table->string('module', 50)->index();
                $table->timestamps();
            }
        );

        Schema::create('plan_translations', function (Blueprint $table) {
            $table->id();
            $table->uuid('plan_id')->index();
            $table->string('locale', 10)->index();
            $table->string('name');
            $table->text('description')->nullable();

            $table->unique(['plan_id', 'locale'], 'plan_translations_unique');
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->onDelete('cascade');
        });
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
