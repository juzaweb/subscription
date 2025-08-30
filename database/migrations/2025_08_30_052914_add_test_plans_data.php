<?php

use Illuminate\Database\Migrations\Migration;
use Juzaweb\Modules\Subscription\Models\Plan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $plans = [
            [
                'en' => [
                    'name' => 'Free Plan',
                    'description' => 'This is a Free subscription plan.',
                ],
                'price' => 0,
                'duration' => 0,
                'is_free' => true,
                'module' => 'test',
                'status' => \Juzaweb\Modules\Subscription\Enums\PlanStatus::ACTIVE,
            ],
            [
                'en' => [
                    'name' => 'Basic Plan',
                    'description' => 'This is a basic subscription plan.',
                ],
                'price' => 9.99,
                'duration' => 1,
                'duration_unit' => 'month',
                'module' => 'test',
                'status' => \Juzaweb\Modules\Subscription\Enums\PlanStatus::ACTIVE,
            ],
            [
                'en' => [
                    'name' => 'Premium Plan',
                    'description' => 'This is a premium subscription plan with more features.',
                ],
                'price' => 19.99,
                'duration' => 1,
                'duration_unit' => 'month',
                'module' => 'test',
                'status' => \Juzaweb\Modules\Subscription\Enums\PlanStatus::ACTIVE,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Plan::where(['module' => 'test'])->delete();
    }
};
