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
        $prefix = DB::getTablePrefix();

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
            function (Blueprint $table) use ($prefix) {
                $table->unsignedBigInteger('site_id')->index()->nullable();
                $table->dropUnique("{$prefix}_user_subscriptions_module_user_unique");
                $table->dropForeign("{$prefix}_user_subscription_plan_foreign");
                $table->dropUnique("{$prefix}_user_subscriptions_plan_user_unique");

                $table->unique(['module', 'user_id', 'site_id'], "{$prefix}_user_subscriptions_module_user_unique");
                $table->unique(['plan_id', 'user_id', 'site_id'], "{$prefix}_user_subscriptions_plan_user_unique");

                $table->foreign('plan_id', "{$prefix}_user_subscription_plan_foreign")
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
