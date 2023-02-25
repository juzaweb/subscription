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
    public function up()
    {
        Schema::create(
            'subscription_payment_histories',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('method', 50);
                $table->unsignedBigInteger('user_subscription_id');
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();

                $table->foreign('user_subscription_id')
                    ->references('id')
                    ->on('membership_user_subscriptions')
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
        Schema::dropIfExists('subscription_payment_histories');
    }
};
