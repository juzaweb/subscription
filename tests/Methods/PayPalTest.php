<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Methods\PayPal;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\PlanSubscriptionMethod;
use Juzaweb\Modules\Subscription\Tests\TestCase;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MockPayPal extends PayPal
{
    protected PayPalClient $mockClient;

    public function __construct(PayPalClient $mockClient)
    {
        $this->mockClient = $mockClient;
    }

    protected function getProvider(): PayPalClient
    {
        return $this->mockClient;
    }

    public function createPlan(Plan $plan, array $options = []): PlanSubscriptionMethod
    {
        $method = new PlanSubscriptionMethod;
        $method->payment_plan_id = 'P-12345';
        $method->data = ['product_id' => 'PROD-12345'];

        return $method;
    }
}

class PayPalTest extends TestCase
{
    public function test_subscribe()
    {
        $plan = new Plan;
        $plan->name = 'Test Plan';
        $plan->description = 'Test Description';

        $options = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'return_url' => 'https://example.com/return',
            'cancel_url' => 'https://example.com/cancel',
        ];

        // Mock PayPalClient
        $mockClient = $this->createMock(PayPalClient::class);

        // Setup the chained method calls
        $mockClient->expects($this->once())
            ->method('addProductById')
            ->with('PROD-12345')
            ->willReturn($mockClient);

        $mockClient->expects($this->once())
            ->method('addBillingPlanById')
            ->with('P-12345')
            ->willReturn($mockClient);

        $mockClient->expects($this->once())
            ->method('setReturnAndCancelUrl')
            ->with('https://example.com/return', 'https://example.com/cancel')
            ->willReturn($mockClient);

        $mockClient->expects($this->once())
            ->method('setupSubscription')
            ->with('John Doe', 'john@example.com')
            ->willReturn([
                'id' => 'I-12345',
                'links' => [
                    [
                        'rel' => 'approve',
                        'href' => 'https://example.com/approve',
                    ],
                ],
            ]);

        $paypal = new MockPayPal($mockClient);

        $result = $paypal->subscribe($plan, $options);

        $this->assertInstanceOf(SubscribeResult::class, $result);
        $this->assertEquals('I-12345', $result->getTransactionId());
        $this->assertEquals('https://example.com/approve', $result->getRedirectUrl());
    }
}
