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
            'subscription_plans',
            function (Blueprint $table) {
                $table->dropColumn(['features']);
            }
        );

        Schema::table(
            'subscription_plan_features',
            function (Blueprint $table) {
                $table->string('value')->nullable();
                $table->string('feature_key', 100)->index()->nullable();
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
            'subscription_plan_features',
            function (Blueprint $table) {
                $table->dropColumn(['value', 'feature_key']);
            }
        );
    }
};
