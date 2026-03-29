<?php

namespace Juzaweb\Modules\Subscription\Tests\Http\Controllers\API;

use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Tests\TestCase;

class PlanControllerTest extends TestCase
{
    public function test_index_returns_active_plans()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'price' => 10,
            'is_free' => false,
            'module' => 'test',
            'active' => true,
            'duration' => 1,
            'duration_unit' => 'month',
        ]);

        $response = $this->getJson('/api/v1/subscription/plans');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'is_free',
                    'duration',
                    'duration_unit',
                    'features',
                    'active',
                ],
            ],
            'meta',
            'links',
        ]);

        $planIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($plan->id, $planIds);
    }
}
