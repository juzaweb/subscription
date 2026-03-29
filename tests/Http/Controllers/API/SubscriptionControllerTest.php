<?php

namespace Juzaweb\Modules\Subscription\Tests\Http\Controllers\API;

use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\Subscription;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    public function test_index_returns_user_subscriptions()
    {
        $user = User::factory()->create();
        $this->actingAs($user); // Remove 'api' guard

        $plan = Plan::create([
            'name' => 'Test Plan',
            'price' => 10,
            'is_free' => false,
            'module' => 'test',
            'active' => true,
            'duration' => 1,
            'duration_unit' => 'month',
        ]);

        $method = SubscriptionMethod::create([
            'name' => 'Test Method',
            'driver' => 'paypal',
            'active' => true,
        ]);

        $subscription = Subscription::create([
            'billable_id' => $user->id,
            'billable_type' => get_class($user),
            'plan_id' => $plan->id,
            'method_id' => $method->id,
            'status' => 'active',
            'amount' => 10,
            'agreement_id' => 'I-123456',
            'module' => 'test',
            'driver' => 'paypal',
        ]);

        $response = $this->getJson('/api/v1/subscription/subscriptions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'plan_id',
                    'method_id',
                    'amount',
                    'start_date',
                    'end_date',
                    'status',
                    'plan',
                    'method',
                ],
            ],
            'meta',
            'links',
        ]);

        $this->assertEquals($subscription->id, $response->json('data.0.id'));
    }
}
