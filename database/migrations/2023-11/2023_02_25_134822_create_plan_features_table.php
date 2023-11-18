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
            'subscription_plan_features',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->string('description', 250)->nullable();
                $table->unsignedBigInteger('plan_id');

                $table->foreign('plan_id')
                    ->references('id')
                    ->on('subscription_plans')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('subscription_plan_features');
    }
};
