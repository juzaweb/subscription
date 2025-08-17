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
            'plan_features',
            function (Blueprint $table) {
                $table->id();
                $table->uuid('plan_id')->index();
                $table->string('name', 50)->index();
                $table->foreign('plan_id')
                    ->references('id')
                    ->on('plans')
                    ->onDelete('cascade');
                $table->timestamps();

                $table->unique(['plan_id', 'name']);
            }
        );

        Schema::create(
            'plan_feature_translations',
            function (Blueprint $table) {
                $table->id();
                $table->uuid('plan_feature_id')->index();
                $table->string('locale', 10)->index();
                $table->text('description')->nullable();

                $table->unique(['plan_feature_id', 'locale'], 'plan_feature_translations_unique');
                $table->foreign('plan_feature_id')
                    ->references('id')
                    ->on('plan_features')
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
        Schema::dropIfExists('plan_feature_translations');
        Schema::dropIfExists('plan_features');
    }
};
