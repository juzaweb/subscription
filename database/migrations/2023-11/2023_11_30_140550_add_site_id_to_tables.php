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
        Schema::table(
            'subscription_payment_histories',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->nullable();
            }
        );

        Schema::table(
            'subscription_payment_methods',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->nullable();
                $table->unique(['site_id', 'method', 'module']);
            }
        );

        Schema::table(
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->nullable();
                $table->dropUnique('user_subscriptions_module_user_unique');
                $table->dropForeign('user_subscription_plan_foreign');
                $table->dropUnique('user_subscriptions_plan_user_unique');

                $table->unique(['module', 'user_id', 'site_id'], 'user_subscriptions_module_user_unique');
                $table->unique(['plan_id', 'user_id', 'site_id'], 'user_subscriptions_plan_user_unique');

                $table->foreign('plan_id', 'user_subscription_plan_foreign')
                    ->references('id')
                    ->on('subscription_plans');
            }
        );

        Schema::table(
            'subscription_plans',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->nullable();
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
        Schema::table(
            'subscription_payment_histories',
            function (Blueprint $table) {
                $table->dropColumn(['site_id']);
            }
        );

        Schema::table(
            'subscription_payment_methods',
            function (Blueprint $table) {
                $table->dropUnique(['site_id', 'method', 'module']);
                $table->dropColumn(['site_id']);
            }
        );

        Schema::table(
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->dropColumn(['site_id']);
            }
        );

        Schema::table(
            'subscription_plans',
            function (Blueprint $table) {
                $table->dropColumn(['site_id']);
            }
        );
    }
};
