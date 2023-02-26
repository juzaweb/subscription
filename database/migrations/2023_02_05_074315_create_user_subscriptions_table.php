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
            'subscription_user_subscriptions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('token', 100)->unique();
                $table->string('role', 50)->index();
                $table->string('method', 50)->index();
                $table->string('agreement_id', 100)->index();
                $table->string('payer_id', 100);
                $table->string('payer_email', 100);
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
    public function down(): void
    {
        Schema::dropIfExists('subscription_user_subscriptions');
    }
};
