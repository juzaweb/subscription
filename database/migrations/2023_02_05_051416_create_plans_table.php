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
            'subscription_plans',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->float('price')->index()->default(0);
                $table->string('status', 50)->index()->default('draft');
                $table->timestamps();
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
        Schema::dropIfExists('subscription_packages');
    }
};
