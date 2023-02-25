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
            'subscription_plan_payment_methods',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('plan_key', 150)->unique();
                $table->string('method', 50)->index();
                $table->unsignedBigInteger('plan_id');

                $table->foreign('plan_id')
                    ->references('id')
                    ->on('plans')
                    ->onDelete('cascade');
                $table->unique(['method', 'plan_id']);
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
        Schema::dropIfExists('subscription_plan_payment_methods');
    }
};
