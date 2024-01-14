<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Juzaweb\Subscription\Models\ModuleSubscription;

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
            'subscription_module_subscriptions',
            function (Blueprint $table) use ($prefix) {
                $table->bigIncrements('id');
                $table->uuid();
                $table->unsignedBigInteger('module_id');
                $table->string('module_type', 50);

                $table->string('agreement_id', 100)->unique()
                    ->comment('Agreement of payment partner');
                $table->float('amount')->index();
                $table->dateTime('start_date')->index()->nullable();
                $table->dateTime('end_date')->index()->nullable();
                $table->unsignedBigInteger('method_id');
                $table->unsignedBigInteger('plan_id');
                $table->unsignedBigInteger('register_by');
                $table->string('status', 20)->default(ModuleSubscription::STATUS_PENDING)->index();
                $table->unsignedBigInteger('site_id')->default(0)->index();
                $table->timestamps();

                $table->unique(['module_id', 'module_type']);
                $table->foreign('method_id', "{$prefix}_website_subscription_payment_methods_foreign")
                    ->references('id')
                    ->on('subscription_payment_methods');
                $table->foreign('plan_id', "{$prefix}_website_subscription_plan_foreign")
                    ->references('id')
                    ->on('subscription_plans');
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
        Schema::dropIfExists('subscription_module_subscriptions');
    }
};
