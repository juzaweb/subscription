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
            'subscriptions',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('agreement_id', 100)->unique()
                    ->comment('Agreement of payment partner');
                $table->decimal('amount', 15, 2)->index();
                $table->string('module', 50)->index();
                $table->string('driver', 50)->index();
                $table->dateTime('start_date')->index()->nullable();
                $table->dateTime('end_date')->index()->nullable();
                $table->uuid('method_id')->nullable();
                $table->uuid('plan_id');
                $table->uuid('user_id')->index();
                $table->string('status', 50)->default('active')->index();
                $table->timestamps();

                $table->foreign('plan_id')
                    ->references('id')
                    ->on('plans')
                    ->onDelete('cascade');

                $table->foreign('method_id')
                    ->references('id')
                    ->on('subscription_methods');

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
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
        Schema::dropIfExists('subscriptions');
    }
};
