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
                $table->id();
                $table->string('transaction_id', 100)->unique();
                $table->string('method', 50)->index();
                $table->string('module', 50)->index();
                $table->string('type', 50)->index();
                $table->float('amount')->index();
                $table->string('agreement_id', 100)->index()
                    ->comment('Agreement of payment partner');
                $table->dateTime('end_date')->nullable()->index();
                $table->unsignedBigInteger('method_id')->nullable();
                $table->uuid('plan_id')->nullable();
                $table->uuid('user_id')->index();
                $table->unsignedBigInteger('subscription_id');
                $table->timestamps();

                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->onDelete('cascade');

                
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
