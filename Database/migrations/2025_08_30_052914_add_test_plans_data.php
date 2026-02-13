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
                'name' => 'Basic Plan',
                'price' => 9.99,
                'duration' => 1,
                'duration_unit' => 'month',
                'module' => 'test',
                'active' => true,
            ],
            [
                'name' => 'Free Plan',
                'price' => 0,
                'duration' => 0,
                'duration_unit' => 'month',
                'is_free' => true,
                'module' => 'test',
                'active' => true,
            ],
            [
                'name' => 'Premium Plan',
                'price' => 19.99,
                'duration' => 1,
                'duration_unit' => 'month',
                'module' => 'test',
                'active' => true,
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
