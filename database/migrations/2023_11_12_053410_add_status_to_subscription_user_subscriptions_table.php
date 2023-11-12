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
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->string('status', 20)->default('active');
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
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->dropColumn(['status']);
            }
        );
    }
};
