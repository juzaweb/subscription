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
            'subscription_methods',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('driver', 50)->unique();
                $table->json('config')->nullable();
                $table->timestamps();
            }
        );

        Schema::create(
            'subscription_method_translations',
            function (Blueprint $table) {
                $table->id();
                $table->uuid('subscription_method_id')->index();
                $table->string('locale', 10)->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unique(['subscription_method_id', 'locale'], 'subscription_method_translations_unique');
                $table->timestamps();

                $table->foreign('subscription_method_id')
                    ->references('id')
                    ->on('subscription_methods')
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
        Schema::dropIfExists('subscription_method_translations');
        Schema::dropIfExists('subscription_methods');
    }
};
