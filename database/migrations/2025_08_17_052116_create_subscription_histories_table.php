<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'subscription_histories',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('method', 50)->index();
                $table->string('module', 50)->index();
                $table->decimal('amount', 15, 2)->index();
                $table->string('agreement_id', 100)->index()
                    ->comment('Agreement of payment partner');
                $table->dateTime('end_date')->nullable()->index();
                $table->uuid('method_id')->nullable();
                $table->uuid('plan_id')->nullable();
                $table->uuid('user_id')->index();
                $table->uuid('subscription_id');
                $table->string('status', 50)->default('processing');
                $table->json('data')->nullable();
                $table->timestamps();

                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->onDelete('cascade');

                $table->foreign('method_id')
                    ->references('id')
                    ->on('subscription_methods')
                    ->onDelete('set null');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_histories');
    }
};
