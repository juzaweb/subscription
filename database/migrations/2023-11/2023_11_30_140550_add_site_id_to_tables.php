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
                $table->unsignedBigInteger('site_id')->index()->default(0);
            }
        );

        Schema::table(
            'subscription_payment_methods',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->default(0);
                $table->unique(['site_id', 'method', 'module']);
            }
        );

        Schema::table(
            'subscription_plans',
            function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->index()->default(0);
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
            'subscription_plans',
            function (Blueprint $table) {
                $table->dropColumn(['site_id']);
            }
        );
    }
};
