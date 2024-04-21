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
            function (Blueprint $table) use ($prefix) {
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('module_subscription_id');
                $table->foreign('module_subscription_id', "{$prefix}payment_histories_module_subscription_id_foreign")
                    ->references('id')
                    ->on('subscription_module_subscriptions')
                    ->onDelete('set null');
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
        $prefix = DB::getTablePrefix();

        Schema::table(
            'subscription_payment_histories',
            function (Blueprint $table) use ($prefix) {
                $table->dropForeign("{$prefix}payment_histories_module_subscription_id_foreign");
                $table->dropColumn(['module_subscription_id', 'module_id']);
            }
        );
    }
};
