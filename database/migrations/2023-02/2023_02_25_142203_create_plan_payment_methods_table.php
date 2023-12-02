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

        Schema::create(
            'subscription_plan_payment_methods',
            function (Blueprint $table) use ($prefix) {
                $table->bigIncrements('id');
                $table->string('payment_plan_id', 150)->unique()
                    ->comment('Plan id of payment service');
                $table->string('method', 50)->index();
                $table->unsignedBigInteger('plan_id');
                $table->unsignedBigInteger('method_id');

                $table->foreign('plan_id')
                    ->references('id')
                    ->on('subscription_plans')
                    ->onDelete('cascade');

                $table->foreign('method_id')
                    ->references('id')
                    ->on('subscription_payment_methods')
                    ->onDelete('cascade');

                $table->unique(['method_id', 'plan_id'], "{$prefix}_sppm_method_id_plan_id_unique");
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
        Schema::dropIfExists('subscription_plan_payment_methods');
    }
};
