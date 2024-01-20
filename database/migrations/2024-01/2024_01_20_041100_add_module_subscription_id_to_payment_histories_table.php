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
                $table->unsignedBigInteger('module_id')->nullable();
                $table->foreignId('module_subscription_id')
                    ->nullable()
                    ->constrained('subscription_module_subscriptions')
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
        Schema::table(
            'subscription_payment_histories',
            function (Blueprint $table) {
                $table->dropForeign(['module_subscription_id']);
                $table->dropColumn(['module_subscription_id', 'module_id']);
            }
        );
    }
};
