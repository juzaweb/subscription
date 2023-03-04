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
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid()->unique();
                $table->string('agreement_id', 100)->index()
                    ->comment('Agreement of payment partner');
                $table->float('amount')->index();
                $table->string('module', 50)->index();
                $table->unsignedBigInteger('method_id');
                $table->unsignedBigInteger('plan_id');
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();

                $table->foreign('method_id')
                    ->references('id')
                    ->on('subscription_payment_methods');
                $table->foreign('plan_id')
                    ->references('id')
                    ->on('subscription_plans');
            }
        );

        Schema::create(
            'subscription_user_subscription_metas',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_subscription_id');
                $table->string('meta_key', 50)->index();
                $table->text('meta_value')->nullable();
                $table->unique(['user_subscription_id', 'meta_key'], 'user_subscription_meta_key_unique');
                $table->foreign('user_subscription_id', 'subscription_user_metas_foreign')
                    ->references('id')
                    ->on('subscription_user_subscriptions')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('subscription_user_subscription_metas');
        Schema::dropIfExists('subscription_user_subscriptions');
    }
};
