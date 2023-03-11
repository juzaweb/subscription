<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'subscription_plans',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid()->unique();
                $table->string('name');
                $table->string('description', 250)->nullable();
                $table->float('price')->index()->default(0);
                $table->boolean('is_free')->default(false)->index();
                $table->string('status', 50)->index()->default('draft');
                $table->string('module', 50)->index();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
