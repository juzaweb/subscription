<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Methods\PayPal;
use Juzaweb\Modules\Subscription\Models\Plan;
use Mockery;
use PHPUnit\Framework\TestCase;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MockPayPal extends PayPal
{
    protected PayPalClient $mockProvider;

    public function setMockProvider(PayPalClient $mockProvider)
    {
        $this->mockProvider = $mockProvider;
    }

    protected function getProvider(): PayPalClient
    {
        return $this->mockProvider;
    }
}

class PayPalTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_plan_throws_exception_on_plan_error()
    {
        $plan = Mockery::mock(Plan::class)->makePartial();
        $plan->name = 'Test Plan';
        $plan->description = 'Test Description';
        $plan->price = 10;

        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('where')->with('method', 'PayPal')->andReturnSelf();
        $relation->shouldReceive('first')->andReturn(null);

        $plan->shouldReceive('subscriptionMethods')->andReturn($relation);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('createProduct')->once()->andReturn(['id' => 'prod_123']);
        $provider->shouldReceive('createPlan')->once()->andReturn(['error' => ['message' => 'Custom PayPal plan error']]);

        $driver = new MockPayPal;
        $driver->setMockProvider($provider);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Custom PayPal plan error');

        $driver->createPlan($plan);
    }

    public function test_create_plan_throws_exception_on_product_error()
    {
        $plan = Mockery::mock(Plan::class)->makePartial();
        $plan->name = 'Test Plan';
        $plan->description = 'Test Description';
        $plan->price = 10;

        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('where')->with('method', 'PayPal')->andReturnSelf();
        $relation->shouldReceive('first')->andReturn(null);

        $plan->shouldReceive('subscriptionMethods')->andReturn($relation);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('createProduct')->once()->andReturn(['error' => ['message' => 'Custom PayPal product error']]);
        $provider->shouldNotReceive('createPlan');

        $driver = new MockPayPal;
        $driver->setMockProvider($provider);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Custom PayPal product error');

        $driver->createPlan($plan);
    }
}
