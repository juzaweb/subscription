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
            'subscription_payment_histories',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('token', 100)->unique()
                    ->comment('Token of payment partner');
                $table->string('method', 50)->index();
                $table->string('module', 50)->index();
                $table->string('agreement_id', 100)->index()
                    ->comment('Agreement of payment partner');
                $table->unsignedBigInteger('method_id');
                $table->unsignedBigInteger('plan_id');
                $table->unsignedBigInteger('user_subscription_id')->nullable();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();

                $table->foreign('user_subscription_id')
                    ->references('id')
                    ->on('subscription_user_subscriptions')
                    ->onDelete('set null');
                $table->foreign('method_id')
                    ->references('id')
                    ->on('subscription_payment_methods');
                $table->foreign('plan_id')
                    ->references('id')
                    ->on('subscription_plans');
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
        Schema::dropIfExists('subscription_payment_histories');
    }
};
