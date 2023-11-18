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
            'subscription_payment_methods',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('method', 50)->index();
                $table->string('description', 250)->nullable();
                $table->json('configs')->nullable();
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
        Schema::dropIfExists('subscription_payment_methods');
    }
};
