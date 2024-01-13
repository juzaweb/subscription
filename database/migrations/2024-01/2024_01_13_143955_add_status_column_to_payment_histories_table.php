<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Juzaweb\Subscription\Models\PaymentHistory;

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
                $table->string('status', 20)->default(PaymentHistory::STATUS_ACTIVE);
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
                $table->dropColumn(['status']);
            }
        );
    }
};
