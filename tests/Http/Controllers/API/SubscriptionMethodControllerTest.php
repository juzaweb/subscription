<?php

namespace Juzaweb\Modules\Subscription\Tests\Http\Controllers\API;

use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Tests\TestCase;

class SubscriptionMethodControllerTest extends TestCase
{
    public function test_index_returns_active_subscription_methods()
    {
        $method = SubscriptionMethod::create([
            'name' => 'Test Method',
            'driver' => 'paypal',
            'active' => true,
        ]);

        $response = $this->getJson('/api/v1/subscription/methods');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'driver',
                    'active',
                ],
            ],
            'meta',
            'links',
        ]);

        $methodIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($method->id, $methodIds);
    }
}
