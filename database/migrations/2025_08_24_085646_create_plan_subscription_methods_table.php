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
            'plan_subscription_methods',
            function (Blueprint $table) {
                $table->id();
                $table->string('payment_plan_id', 150)->unique()->comment('Plan id of payment service');
                $table->uuid('plan_id');
                $table->string('method', 50)->index()
                    ->comment('Subscription method, e.g. paypal, stripe, etc.');
                $table->json('data')->nullable();
                $table->foreign('plan_id')
                    ->references('id')
                    ->on('plans')
                    ->onDelete('cascade');

                $table->unique(['method', 'plan_id']);
                $table->timestamps();
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
        Schema::dropIfExists('plan_subscription_methods');
    }
};
