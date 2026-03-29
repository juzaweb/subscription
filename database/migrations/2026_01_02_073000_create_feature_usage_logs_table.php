<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name');
            $table->date('usage_date');
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['feature_name', 'usage_date'], 'feature_usage_unique');
            $table->index(['usage_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_usage_logs');
    }
};
