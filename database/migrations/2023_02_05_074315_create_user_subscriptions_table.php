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
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('token', 100)->unique();
                $table->string('role', 50)->index();
                $table->string('method', 50)->index();
                $table->string('agreement_id')->index();
                $table->string('payer_id');
                $table->string('payer_email');
                $table->float('amount')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();
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
        Schema::dropIfExists('subscription_user_subscriptions');
    }
};
